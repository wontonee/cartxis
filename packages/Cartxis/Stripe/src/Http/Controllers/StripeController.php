<?php

namespace Cartxis\Stripe\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Cartxis\Shop\Models\Order;
use Cartxis\Core\Models\EmailTemplate;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Core\Services\PaymentGatewayManager;
use Cartxis\Sales\Services\InvoiceService;
use Cartxis\Sales\Services\TransactionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class StripeController extends Controller
{
    protected PaymentGatewayManager $gatewayManager;
    protected InvoiceService $invoiceService;
    protected TransactionService $transactionService;

    public function __construct(
        PaymentGatewayManager $gatewayManager,
        InvoiceService $invoiceService,
        TransactionService $transactionService
    )
    {
        $this->gatewayManager = $gatewayManager;
        $this->invoiceService = $invoiceService;
        $this->transactionService = $transactionService;
    }

    /**
     * Payment success callback.
     */
    public function paymentSuccess(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // Verify payment with Stripe gateway
        $gateway = $this->gatewayManager->get('stripe');
        
        if (!$gateway) {
            return redirect()->route('cart.index')
                ->with('error', 'Payment gateway not found');
        }

        $result = $gateway->handleCallback([
            'session_id' => $request->get('session_id'),
        ]);

        if (!$result['success']) {
            return redirect()->route('cart.index')
                ->with('error', 'Payment verification failed: ' . $result['message']);
        }

        // Update order payment status
        $order->update([
            'payment_status' => Order::PAYMENT_PAID,
            'status' => Order::STATUS_PROCESSING,
        ]);

        $invoice = $this->invoiceService->createFromOrderIfMissing($order);
        $this->transactionService->createPaymentIfMissing($order, [
            'payment_method' => 'stripe',
            'gateway' => 'stripe',
            'gateway_transaction_id' => $result['transaction_id'] ?? null,
            'amount' => $order->total,
            'status' => 'completed',
            'notes' => 'Payment completed via Stripe',
            'invoice_id' => $invoice?->id,
        ]);

        // Send order confirmation email
        try {
            $template = EmailTemplate::findByCode('order_placed');
            
            if ($template) {
                $shippingAddress = $order->shippingAddress;
                $customerName = $shippingAddress
                    ? trim($shippingAddress->first_name . ' ' . $shippingAddress->last_name)
                    : ($order->customer_name ?? 'Customer');
                
                $template->send($order->customer_email, [
                    'customer_name' => $customerName,
                    'order_number' => $order->order_number,
                    'order_date' => $order->created_at->format('F j, Y'),
                    'order_total' => '₹' . number_format($order->total, 2),
                    'store_name' => config('app.name', 'Cartxis'),
                    'store_url' => url('/'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Order confirmation email failed after Stripe payment', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        // Keep last order marker for guest success page authorization
        Session::put('checkout.last_order_id', $order->id);

        // Redirect to order success page
        return redirect()->route('shop.checkout.success', ['order' => $order->id])
            ->with('success', 'Payment successful! Your order has been confirmed.');
    }

    /**
     * Payment cancel callback.
     */
    public function paymentCancel($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Update order status to payment failed
        $order->update([
            'payment_status' => Order::PAYMENT_FAILED,
        ]);

        // Redirect back to cart with error
        return redirect()->route('cart.index')
            ->with('error', 'Payment was cancelled. Your order has been saved and you can try again later.');
    }

    /**
     * Create Stripe payment intent (Legacy - keeping for compatibility).
     */
    public function createPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.50',
            'currency' => 'required|string|size:3',
            'order_id' => 'required|string',
            'customer_email' => 'required|email',
            'description' => 'nullable|string',
        ]);

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($validated['amount'] * 100), // Convert to cents
                'currency' => strtolower($validated['currency']),
                'description' => $validated['description'] ?? "Order {$validated['order_id']}",
                'metadata' => [
                    'order_id' => $validated['order_id'],
                    'customer_email' => $validated['customer_email'],
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Handle Stripe webhook.
     */
    public function webhook(Request $request)
    {
        $payload    = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        // Prefer webhook secret stored in DB (admin UI) over .env fallback
        $dbMethod = PaymentMethod::where('code', 'stripe')->where('is_active', true)->first();
        $dbSecret = null;
        if ($dbMethod) {
            $mode     = $dbMethod->getConfigValue('mode', 'live');
            $dbKey    = $mode === 'test' ? 'test_webhook_secret' : 'webhook_secret';
            $dbSecret = $dbMethod->getConfigValue($dbKey) ?: $dbMethod->getConfigValue('webhook_secret');
        }
        $webhook_secret = $dbSecret ?: config('stripe.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $webhook_secret);

            match ($event->type) {
                'payment_intent.succeeded' => $this->handlePaymentSuccess($event),
                'payment_intent.payment_failed' => $this->handlePaymentFailed($event),
                'charge.refunded' => $this->handleRefund($event),
                default => null
            };

            return response()->json(['status' => 'received']);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: invalid payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Webhook error'], 400);
        }
    }

    /**
     * Handle successful payment.
     */
    protected function handlePaymentSuccess($event)
    {
        $paymentIntent = $event->data->object;
        $orderId = $paymentIntent->metadata->order_id ?? null;

        if ($orderId) {
            $order = Order::find($orderId);
            if ($order && !in_array($order->status, ['paid', 'processing', 'shipped', 'completed'])) {
                $order->update([
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'payment_gateway_transaction_id' => $paymentIntent->id,
                ]);
            }
        }
    }

    /**
     * Handle failed payment.
     */
    protected function handlePaymentFailed($event)
    {
        $paymentIntent = $event->data->object;
        $orderId = $paymentIntent->metadata->order_id ?? null;

        if ($orderId) {
            $order = Order::find($orderId);
            if ($order && !in_array($order->status, ['paid', 'shipped', 'completed'])) {
                $order->update([
                    'status' => 'payment_failed',
                    'payment_status' => 'failed',
                ]);
            }
        }
    }

    /**
     * Handle refund.
     */
    protected function handleRefund($event)
    {
        $charge = $event->data->object;
        $paymentIntentId = $charge->payment_intent ?? null;

        if ($paymentIntentId) {
            $order = Order::where('payment_gateway_transaction_id', $paymentIntentId)->first();
            if ($order) {
                $order->update([
                    'status' => 'refunded',
                    'payment_status' => 'refunded',
                ]);
            }
        }
    }
}
