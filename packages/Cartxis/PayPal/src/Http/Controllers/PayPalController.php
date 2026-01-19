<?php

namespace Cartxis\PayPal\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Cartxis\Core\Services\PaymentGatewayManager;
use Cartxis\Shop\Models\Order;
use Cartxis\Sales\Services\TransactionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PayPalController extends Controller
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager,
        protected TransactionService $transactionService
    ) {}

    /**
     * Handle PayPal callback after customer returns from PayPal.
     */
    public function callback(Request $request)
    {
        Log::info('PayPal callback received', $request->all());

        try {
            $orderId = $request->query('order');
            $paypalOrderId = $request->query('token');

            if (!$orderId || !$paypalOrderId) {
                return redirect()->route('checkout.index')->with('error', 'Invalid payment callback');
            }

            $order = Order::find($orderId);

            if (!$order) {
                return redirect()->route('checkout.index')->with('error', 'Order not found');
            }

            // Get PayPal gateway
            $gateway = $this->gatewayManager->get('paypal');

            if (!$gateway) {
                return redirect()->route('checkout.index')->with('error', 'PayPal gateway not found');
            }

            // Handle callback
            $result = $gateway->handleCallback([
                'token' => $paypalOrderId,
                'order_id' => $orderId,
            ]);

            if ($result['success']) {
                // Create transaction
                DB::transaction(function () use ($order, $result) {
                    $this->transactionService->createPayment($order, [
                        'payment_method' => 'paypal',
                        'gateway' => 'paypal',
                        'gateway_transaction_id' => $result['transaction_id'],
                        'amount' => $order->grand_total,
                        'status' => 'completed',
                        'response_data' => $result['response_data'] ?? null,
                        'notes' => 'Payment completed via PayPal',
                    ]);

                    // Update order status
                    $order->update([
                        'status' => 'processing',
                        'payment_status' => 'paid',
                        'payment_gateway_data' => array_merge(
                            $order->payment_gateway_data ?? [],
                            $result['response_data'] ?? []
                        ),
                    ]);
                });

                Log::info('PayPal payment completed', [
                    'order_id' => $order->id,
                    'transaction_id' => $result['transaction_id'],
                ]);

                return redirect()->route('checkout.success', ['order' => $order->id])
                    ->with('success', 'Payment completed successfully!');
            }

            Log::warning('PayPal payment failed', [
                'order_id' => $order->id,
                'message' => $result['message'] ?? 'Unknown error',
            ]);

            return redirect()->route('checkout.index')
                ->with('error', $result['message'] ?? 'Payment failed. Please try again.');
        } catch (\Exception $e) {
            Log::error('PayPal callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('checkout.index')
                ->with('error', 'Payment processing failed. Please try again.');
        }
    }

    /**
     * Handle PayPal webhook notifications.
     */
    public function webhook(Request $request)
    {
        Log::info('PayPal webhook received', $request->all());

        try {
            // PayPal webhook signature verification would go here
            // For now, we'll just log the webhook data

            $event = $request->input('event_type');
            $resource = $request->input('resource', []);

            Log::info('PayPal webhook event', [
                'event_type' => $event,
                'resource_id' => $resource['id'] ?? null,
            ]);

            // Handle different webhook events
            switch ($event) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    // Payment captured successfully
                    // Update order status if needed
                    break;

                case 'PAYMENT.CAPTURE.DENIED':
                    // Payment was denied
                    break;

                case 'PAYMENT.CAPTURE.REFUNDED':
                    // Payment was refunded
                    break;
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('PayPal webhook error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }
}
