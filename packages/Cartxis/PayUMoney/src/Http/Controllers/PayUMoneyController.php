<?php

namespace Cartxis\PayUMoney\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Cartxis\Core\Services\PaymentGatewayManager;
use Cartxis\Shop\Models\Order;
use Cartxis\Sales\Services\InvoiceService;
use Cartxis\Sales\Services\TransactionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PayUMoneyController extends Controller
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager,
        protected InvoiceService $invoiceService,
        protected TransactionService $transactionService
    ) {}

    /**
     * Handle PayUMoney callback (success, failure, cancel).
     */
    public function callback(Request $request)
    {
        try {
            $orderId = $request->input('udf1');
            $status = $request->input('status');

            if (!$orderId) {
                return redirect()->route('shop.checkout.index')->with('error', 'Invalid payment callback');
            }

            $order = Order::find($orderId);

            if (!$order) {
                return redirect()->route('shop.checkout.index')->with('error', 'Order not found');
            }

            // Get PayUMoney gateway
            $gateway = $this->gatewayManager->get('payumoney');

            if (!$gateway) {
                return redirect()->route('shop.checkout.index')->with('error', 'PayUMoney gateway not found');
            }

            // Handle callback
            $result = $gateway->handleCallback($request->all());

            if ($result['success']) {
                // Create transaction
                DB::transaction(function () use ($order, $result) {
                    $invoice = $this->invoiceService->createFromOrderIfMissing($order);

                    $this->transactionService->createPaymentIfMissing($order, [
                        'payment_method' => 'payumoney',
                        'gateway' => 'payumoney',
                        'gateway_transaction_id' => $result['transaction_id'],
                        'amount' => $order->total,
                        'status' => 'completed',
                        'response_data' => $result['response_data'] ?? null,
                        'notes' => 'Payment completed via PayUMoney',
                        'invoice_id' => $invoice?->id,
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

                Session::put('checkout.last_order_id', $order->id);
                Session::forget('cart');

                return redirect()->route('shop.checkout.success', ['order' => $order->id])
                    ->with('success', 'Payment completed successfully!');
            }

            Log::warning('PayUMoney payment failed', [
                'order_id' => $order->id,
                'status' => $status,
                'message' => $result['message'] ?? 'Unknown error',
            ]);

            return redirect()->route('shop.checkout.index')
                ->with('error', $result['message'] ?? 'Payment failed. Please try again.');
        } catch (\Exception $e) {
            Log::error('PayUMoney callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('shop.checkout.index')
                ->with('error', 'Payment processing failed. Please try again.');
        }
    }

    /**
     * Handle PayUMoney IPN (server-to-server webhook) notification.
     * PayUMoney posts the same hash-signed payload directly to this endpoint.
     * Hash algorithm: SHA-512 over reverse field sequence
     * (salt|status|udf10...udf1|email|firstname|productinfo|amount|txnid|key)
     */
    public function webhook(Request $request)
    {
        try {
            $gateway = $this->gatewayManager->get('payumoney');

            if (!$gateway) {
                Log::error('PayUMoney: Gateway not found for IPN webhook');
                return response()->json(['status' => 'error'], 500);
            }

            // handleCallback() performs SHA-512 hash verification before processing
            $result = $gateway->handleCallback($request->all());

            if ($result['success']) {
                $order = Order::find($result['order_id']);

                if ($order) {
                    DB::transaction(function () use ($order, $result) {
                        // Idempotent — skip if already paid (redirect callback may have arrived first)
                        if ($order->payment_status === 'paid') {
                            return;
                        }

                        $invoice = $this->invoiceService->createFromOrderIfMissing($order);

                        $this->transactionService->createPaymentIfMissing($order, [
                            'payment_method' => 'payumoney',
                            'gateway'        => 'payumoney',
                            'gateway_transaction_id' => $result['transaction_id'],
                            'amount'         => $order->total,
                            'status'         => 'completed',
                            'response_data'  => $result['response_data'] ?? null,
                            'notes'          => 'Payment confirmed via PayUMoney IPN webhook',
                            'invoice_id'     => $invoice?->id,
                        ]);

                        $order->update([
                            'status'         => 'processing',
                            'payment_status' => 'paid',
                            'payment_gateway_data' => array_merge(
                                $order->payment_gateway_data ?? [],
                                $result['response_data'] ?? []
                            ),
                        ]);
                    });
                }

                Log::info('PayUMoney: IPN webhook processed successfully', [
                    'order_id' => $result['order_id'] ?? null,
                ]);

                return response()->json(['status' => 'ok']);
            }

            Log::warning('PayUMoney: IPN webhook hash verification failed', [
                'message' => $result['message'] ?? 'unknown',
            ]);

            return response()->json(['status' => 'invalid_hash'], 400);

        } catch (\Exception $e) {
            Log::error('PayUMoney: IPN webhook error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }
}
