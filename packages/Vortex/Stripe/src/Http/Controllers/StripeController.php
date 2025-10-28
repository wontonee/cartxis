<?php

namespace Vortex\Stripe\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Vortex\Shop\Models\Order;
use Vortex\Core\Models\EmailTemplate;
use Vortex\Core\Services\PaymentGatewayManager;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    protected PaymentGatewayManager $gatewayManager;

    public function __construct(PaymentGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
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
            return redirect()->route('shop.cart.index')
                ->with('error', 'Payment gateway not found');
        }

        $result = $gateway->handleCallback([
            'session_id' => $request->get('session_id'),
        ]);

        if (!$result['success']) {
            return redirect()->route('shop.cart.index')
                ->with('error', 'Payment verification failed: ' . $result['message']);
        }

        // Update order payment status
        $order->update([
            'payment_status' => Order::PAYMENT_PAID,
            'status' => Order::STATUS_PROCESSING,
        ]);

        // Send order confirmation email
        try {
            $template = EmailTemplate::findByCode('order_placed');
            
            if ($template) {
                $shippingAddress = $order->shippingAddress();
                $customerName = $shippingAddress->first_name . ' ' . $shippingAddress->last_name;
                
                $template->send($order->customer_email, [
                    'customer_name' => $customerName,
                    'order_number' => $order->order_number,
                    'order_date' => $order->created_at->format('F j, Y'),
                    'order_total' => '₹' . number_format($order->total, 2),
                    'store_name' => config('app.name', 'Vortex'),
                    'store_url' => url('/'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Order confirmation email failed after Stripe payment', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

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
        return redirect()->route('shop.cart.index')
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
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $webhook_secret = config('stripe.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                $webhook_secret
            );

            // Handle different event types
            match ($event->type) {
                'payment_intent.succeeded' => $this->handlePaymentSuccess($event),
                'payment_intent.payment_failed' => $this->handlePaymentFailed($event),
                'charge.refunded' => $this->handleRefund($event),
                default => null
            };

            return response()->json(['status' => 'received']);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Webhook error'], 400);
        }
    }

    /**
     * Handle successful payment.
     */
    protected function handlePaymentSuccess($event)
    {
        // Future: Update order status to paid
        $paymentIntent = $event->data->object;
        $orderId = $paymentIntent->metadata->order_id ?? null;

        if ($orderId) {
            // \App\Models\Order::find($orderId)?->update(['status' => 'paid']);
        }
    }

    /**
     * Handle failed payment.
     */
    protected function handlePaymentFailed($event)
    {
        // Future: Update order status to payment_failed
        $paymentIntent = $event->data->object;
        $orderId = $paymentIntent->metadata->order_id ?? null;

        if ($orderId) {
            // \App\Models\Order::find($orderId)?->update(['status' => 'payment_failed']);
        }
    }

    /**
     * Handle refund.
     */
    protected function handleRefund($event)
    {
        // Future: Update order status to refunded
    }
}
