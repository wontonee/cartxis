<?php

namespace Cartxis\PayUMoney\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Cartxis\Core\Services\PaymentGatewayManager;
use Cartxis\Shop\Models\Order;
use Cartxis\Sales\Services\TransactionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PayUMoneyController extends Controller
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager,
        protected TransactionService $transactionService
    ) {}

    /**
     * Handle PayUMoney callback (success, failure, cancel).
     */
    public function callback(Request $request)
    {
        Log::info('PayUMoney callback received', $request->all());

        try {
            $orderId = $request->input('udf1');
            $status = $request->input('status');

            if (!$orderId) {
                return redirect()->route('checkout.index')->with('error', 'Invalid payment callback');
            }

            $order = Order::find($orderId);

            if (!$order) {
                return redirect()->route('checkout.index')->with('error', 'Order not found');
            }

            // Get PayUMoney gateway
            $gateway = $this->gatewayManager->get('payumoney');

            if (!$gateway) {
                return redirect()->route('checkout.index')->with('error', 'PayUMoney gateway not found');
            }

            // Handle callback
            $result = $gateway->handleCallback($request->all());

            if ($result['success']) {
                // Create transaction
                DB::transaction(function () use ($order, $result) {
                    $this->transactionService->createPayment($order, [
                        'payment_method' => 'payumoney',
                        'gateway' => 'payumoney',
                        'gateway_transaction_id' => $result['transaction_id'],
                        'amount' => $order->grand_total,
                        'status' => 'completed',
                        'response_data' => $result['response_data'] ?? null,
                        'notes' => 'Payment completed via PayUMoney',
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

                Log::info('PayUMoney payment completed', [
                    'order_id' => $order->id,
                    'transaction_id' => $result['transaction_id'],
                ]);

                return redirect()->route('checkout.success', ['order' => $order->id])
                    ->with('success', 'Payment completed successfully!');
            }

            Log::warning('PayUMoney payment failed', [
                'order_id' => $order->id,
                'status' => $status,
                'message' => $result['message'] ?? 'Unknown error',
            ]);

            return redirect()->route('checkout.index')
                ->with('error', $result['message'] ?? 'Payment failed. Please try again.');
        } catch (\Exception $e) {
            Log::error('PayUMoney callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('checkout.index')
                ->with('error', 'Payment processing failed. Please try again.');
        }
    }
}
