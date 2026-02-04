<?php

namespace Cartxis\PhonePe\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cartxis\Shop\Models\Order;
use Cartxis\Core\Models\EmailTemplate;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Core\Services\PaymentGatewayManager;
use Illuminate\Support\Facades\Log;

class PhonePeController extends Controller
{
    protected PaymentGatewayManager $gatewayManager;

    public function __construct(PaymentGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Payment success callback.
     * Called when user is redirected back after successful payment.
     */
    public function paymentSuccess(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        Log::info('PhonePeController: Payment success callback', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        // Verify payment with PhonePe gateway
        $gateway = $this->gatewayManager->get('phonepe');

        if (!$gateway) {
            return redirect()->route('cart.index')
                ->with('error', 'Payment gateway not found');
        }

        // Verify payment status
        $verified = $gateway->verifyPayment($order);

        if (!$verified) {
            Log::warning('PhonePeController: Payment verification failed', [
                'order_id' => $order->id,
            ]);

            return redirect()->route('cart.index')
                ->with('error', 'Payment verification failed. Please contact support if you were charged.');
        }

        // Update order payment status
        $order->update([
            'payment_status' => Order::PAYMENT_PAID,
            'status' => Order::STATUS_PROCESSING,
        ]);

        Log::info('PhonePeController: Order updated to paid', [
            'order_id' => $order->id,
        ]);

        // Send order confirmation email
        $this->sendOrderConfirmationEmail($order);

        // Redirect to order success page
        return redirect()->route('shop.checkout.success', ['order' => $order->id])
            ->with('success', 'Payment successful! Your order has been confirmed.');
    }

    /**
     * Payment cancel callback.
     * Called when user cancels payment on PhonePe page.
     */
    public function paymentCancel($orderId)
    {
        $order = Order::findOrFail($orderId);

        Log::info('PhonePeController: Payment cancelled', [
            'order_id' => $order->id,
        ]);

        // Update order status to payment failed
        $order->update([
            'payment_status' => Order::PAYMENT_FAILED,
        ]);

        // Redirect back to cart with error
        return redirect()->route('cart.index')
            ->with('error', 'Payment was cancelled. Your order has been saved and you can try again later.');
    }

    /**
     * Handle PhonePe webhook/callback.
     * PhonePe sends payment and refund updates to this endpoint.
     */
    public function webhook(Request $request)
    {
        Log::info('PhonePeController: Webhook received', [
            'headers' => $request->headers->all(),
        ]);

        try {
            // Get payment method for callback credentials
            $paymentMethod = PaymentMethod::where('code', 'phonepe')
                ->where('is_active', true)
                ->first();

            if (!$paymentMethod) {
                Log::error('PhonePeController: Payment method not found or inactive');
                return response()->json(['error' => 'Payment method not configured'], 400);
            }

            $callbackUsername = $paymentMethod->getConfigValue('callback_username');
            $callbackPassword = $paymentMethod->getConfigValue('callback_password');

            if (!$callbackUsername || !$callbackPassword) {
                Log::error('PhonePeController: Callback credentials not configured');
                return response()->json(['error' => 'Callback credentials not configured'], 400);
            }

            // Check if PhonePe SDK is available
            if (!class_exists('\PhonePe\payments\v2\standardCheckout\StandardCheckoutClient')) {
                Log::error('PhonePeController: PhonePe SDK not installed');
                return response()->json(['error' => 'SDK not installed'], 500);
            }

            // Get headers and request body
            $headers = $request->headers->all();
            $requestBody = json_decode($request->getContent(), true);

            // Initialize PhonePe client for verification
            $clientId = $paymentMethod->getConfigValue('client_id');
            $clientSecret = $paymentMethod->getConfigValue('client_secret');
            $clientVersion = (int) $paymentMethod->getConfigValue('client_version', 1);

            $envClass = '\PhonePe\Env';
            $clientClass = '\PhonePe\payments\v2\standardCheckout\StandardCheckoutClient';

            $client = $clientClass::getInstance(
                $clientId,
                $clientVersion,
                $clientSecret,
                $envClass::PRODUCTION
            );

            // Verify callback response
            $callbackResponse = $client->verifyCallbackResponse(
                $headers,
                $requestBody,
                $callbackUsername,
                $callbackPassword
            );

            Log::info('PhonePeController: Callback verified successfully', [
                'response' => json_encode($callbackResponse),
            ]);

            // Process the callback based on event type
            $this->processCallback($callbackResponse);

            // Return 200 OK to acknowledge receipt
            return response()->json(['status' => 'received'], 200);

        } catch (\PhonePe\common\exceptions\PhonePeException $e) {
            Log::error('PhonePeController: Callback verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Verification failed'], 400);

        } catch (\Throwable $e) {
            Log::error('PhonePeController: Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    /**
     * Process callback response and update order status.
     */
    protected function processCallback($callbackResponse): void
    {
        try {
            // Extract order details from callback
            // The exact structure depends on PhonePe SDK response
            $merchantOrderId = $callbackResponse->getMerchantOrderId() ?? null;
            $state = $callbackResponse->getState() ?? null;
            $transactionId = $callbackResponse->getTransactionId() ?? null;

            if (!$merchantOrderId) {
                Log::warning('PhonePeController: No merchant order ID in callback');
                return;
            }

            // Find order by order_number
            $order = Order::where('order_number', $merchantOrderId)->first();

            if (!$order) {
                Log::warning('PhonePeController: Order not found', [
                    'merchant_order_id' => $merchantOrderId,
                ]);
                return;
            }

            Log::info('PhonePeController: Processing callback for order', [
                'order_id' => $order->id,
                'state' => $state,
                'transaction_id' => $transactionId,
            ]);

            // Update order based on payment state
            if ($state === 'COMPLETED') {
                if ($order->payment_status !== Order::PAYMENT_PAID) {
                    $order->update([
                        'payment_status' => Order::PAYMENT_PAID,
                        'status' => Order::STATUS_PROCESSING,
                    ]);

                    // Update payment data with transaction ID
                    $paymentData = json_decode($order->payment_data, true) ?? [];
                    $paymentData['transaction_id'] = $transactionId;
                    $paymentData['callback_state'] = $state;
                    $order->update(['payment_data' => json_encode($paymentData)]);

                    // Send order confirmation email
                    $this->sendOrderConfirmationEmail($order);

                    Log::info('PhonePeController: Order marked as paid via webhook', [
                        'order_id' => $order->id,
                    ]);
                }
            } elseif ($state === 'FAILED') {
                $order->update([
                    'payment_status' => Order::PAYMENT_FAILED,
                ]);

                Log::info('PhonePeController: Order marked as payment failed via webhook', [
                    'order_id' => $order->id,
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('PhonePeController: Error processing callback', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send order confirmation email.
     */
    protected function sendOrderConfirmationEmail(Order $order): void
    {
        try {
            $template = EmailTemplate::findByCode('order_placed');

            if ($template) {
                $shippingAddress = $order->shippingAddress();
                $customerName = $shippingAddress
                    ? $shippingAddress->first_name . ' ' . $shippingAddress->last_name
                    : $order->customer_name ?? 'Customer';

                $template->send($order->customer_email, [
                    'customer_name' => $customerName,
                    'order_number' => $order->order_number,
                    'order_date' => $order->created_at->format('F j, Y'),
                    'order_total' => '₹' . number_format($order->total, 2),
                    'store_name' => config('app.name', 'Cartxis'),
                    'store_url' => url('/'),
                ]);

                Log::info('PhonePeController: Order confirmation email sent', [
                    'order_id' => $order->id,
                    'email' => $order->customer_email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Order confirmation email failed after PhonePe payment', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
