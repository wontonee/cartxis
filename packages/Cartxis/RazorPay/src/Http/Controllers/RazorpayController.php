<?php

namespace Cartxis\Razorpay\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cartxis\Shop\Models\Order;
use Cartxis\Core\Models\EmailTemplate;
use Cartxis\Core\Services\PaymentGatewayManager;
use Illuminate\Support\Facades\Log;

class RazorpayController extends Controller
{
    protected PaymentGatewayManager $gatewayManager;

    public function __construct(PaymentGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Payment callback from Razorpay.
     */
    public function paymentCallback(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // Verify payment with Razorpay gateway
        $gateway = $this->gatewayManager->get('razorpay');
        
        if (!$gateway) {
            return redirect()->route('cart.index')
                ->with('error', 'Payment gateway not found');
        }

        $result = $gateway->handleCallback([
            'razorpay_order_id' => $request->get('razorpay_order_id'),
            'razorpay_payment_id' => $request->get('razorpay_payment_id'),
            'razorpay_signature' => $request->get('razorpay_signature'),
        ]);

        if (!$result['success']) {
            return redirect()->route('cart.index')
                ->with('error', 'Payment verification failed: ' . $result['message']);
        }

        // Store payment ID
        $order->update([
            'payment_gateway_transaction_id' => $result['payment_id'],
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
                    'store_name' => config('app.name', 'Cartxis'),
                    'store_url' => url('/'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Order confirmation email failed after Razorpay payment', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        // Clear cart and checkout session after successful payment
        session()->forget('cart');
        session()->forget('checkout');

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
     * Handle Razorpay webhook.
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Razorpay-Signature');

        Log::info('RazorpayController: Webhook received', [
            'event' => $payload['event'] ?? 'unknown',
        ]);

        if (!$signature) {
            Log::error('RazorpayController: Webhook signature missing');
            return response()->json(['error' => 'Signature missing'], 400);
        }

        // Verify and handle webhook using gateway
        $gateway = $this->gatewayManager->get('razorpay');
        
        if (!$gateway) {
            Log::error('RazorpayController: Gateway not found');
            return response()->json(['error' => 'Gateway not found'], 500);
        }

        $result = $gateway->handleWebhook($payload, $signature);

        if ($result) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['error' => 'Webhook processing failed'], 400);
    }
}
