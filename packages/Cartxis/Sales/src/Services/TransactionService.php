<?php

namespace Cartxis\Sales\Services;

use Cartxis\Sales\Models\Transaction;
use Cartxis\Sales\Repositories\TransactionRepository;
use Cartxis\Shop\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    public function __construct(
        protected TransactionRepository $repository
    ) {}

    /**
     * Create a payment transaction.
     */
    public function createPayment(Order $order, array $data): Transaction
    {
        $transactionData = [
            'order_id' => $order->id,
            'transaction_number' => Transaction::generateTransactionNumber(),
            'type' => 'payment',
            'payment_method' => $data['payment_method'],
            'gateway' => $data['gateway'],
            'gateway_transaction_id' => $data['gateway_transaction_id'] ?? null,
            'amount' => $data['amount'],
            'status' => $data['status'] ?? 'pending',
            'response_data' => $data['response_data'] ?? null,
            'notes' => $data['notes'] ?? null,
            'processed_at' => $data['status'] === 'completed' ? now() : null,
        ];

        $transaction = $this->repository->create($transactionData);

        // Log the transaction
        Log::info('Payment transaction created', [
            'transaction_id' => $transaction->id,
            'order_id' => $order->id,
            'amount' => $transaction->amount,
            'gateway' => $transaction->gateway,
        ]);

        return $transaction;
    }

    /**
     * Create a completed payment transaction if one does not already exist.
     */
    public function createPaymentIfMissing(Order $order, array $data): ?Transaction
    {
        $gateway = $data['gateway'] ?? null;
        $gatewayTransactionId = $data['gateway_transaction_id'] ?? null;

        $query = Transaction::where('order_id', $order->id)
            ->where('type', 'payment');

        if ($gateway) {
            $query->where('gateway', $gateway);
        }

        if ($gatewayTransactionId) {
            $query->where('gateway_transaction_id', $gatewayTransactionId);
        }

        if ($query->exists()) {
            return null;
        }

        $payload = array_merge([
            'payment_method' => $data['payment_method'] ?? ($gateway ?: 'manual'),
            'gateway' => $gateway ?: 'manual',
            'amount' => $data['amount'] ?? $order->total,
            'status' => $data['status'] ?? 'completed',
            'notes' => $data['notes'] ?? 'Payment completed',
            'response_data' => $data['response_data'] ?? null,
            'gateway_transaction_id' => $gatewayTransactionId,
            'invoice_id' => $data['invoice_id'] ?? null,
        ], $data);

        return $this->createPayment($order, $payload);
    }

    /**
     * Create a refund transaction.
     */
    public function createRefund(Order $order, array $data): Transaction
    {
        DB::beginTransaction();
        try {
            $transactionData = [
                'order_id' => $order->id,
                'invoice_id' => $data['invoice_id'] ?? null,
                'credit_memo_id' => $data['credit_memo_id'] ?? null,
                'transaction_number' => Transaction::generateTransactionNumber(),
                'type' => 'refund',
                'payment_method' => $data['payment_method'],
                'gateway' => $data['gateway'],
                'gateway_transaction_id' => $data['gateway_transaction_id'] ?? null,
                'amount' => $data['amount'],
                'status' => $data['status'] ?? 'pending',
                'response_data' => $data['response_data'] ?? null,
                'notes' => $data['notes'] ?? null,
                'processed_at' => $data['status'] === 'completed' ? now() : null,
            ];

            $transaction = $this->repository->create($transactionData);

            // Log the transaction
            Log::info('Refund transaction created', [
                'transaction_id' => $transaction->id,
                'order_id' => $order->id,
                'amount' => $transaction->amount,
                'gateway' => $transaction->gateway,
            ]);

            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create refund transaction', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create an authorization transaction.
     */
    public function createAuthorization(Order $order, array $data): Transaction
    {
        $transactionData = [
            'order_id' => $order->id,
            'transaction_number' => Transaction::generateTransactionNumber(),
            'type' => 'authorization',
            'payment_method' => $data['payment_method'],
            'gateway' => $data['gateway'],
            'gateway_transaction_id' => $data['gateway_transaction_id'] ?? null,
            'amount' => $data['amount'],
            'status' => $data['status'] ?? 'pending',
            'response_data' => $data['response_data'] ?? null,
            'notes' => $data['notes'] ?? null,
            'processed_at' => $data['status'] === 'completed' ? now() : null,
        ];

        $transaction = $this->repository->create($transactionData);

        Log::info('Authorization transaction created', [
            'transaction_id' => $transaction->id,
            'order_id' => $order->id,
            'amount' => $transaction->amount,
        ]);

        return $transaction;
    }

    /**
     * Create a capture transaction.
     */
    public function createCapture(Order $order, array $data): Transaction
    {
        $transactionData = [
            'order_id' => $order->id,
            'transaction_number' => Transaction::generateTransactionNumber(),
            'type' => 'capture',
            'payment_method' => $data['payment_method'],
            'gateway' => $data['gateway'],
            'gateway_transaction_id' => $data['gateway_transaction_id'] ?? null,
            'amount' => $data['amount'],
            'status' => $data['status'] ?? 'pending',
            'response_data' => $data['response_data'] ?? null,
            'notes' => $data['notes'] ?? null,
            'processed_at' => $data['status'] === 'completed' ? now() : null,
        ];

        $transaction = $this->repository->create($transactionData);

        Log::info('Capture transaction created', [
            'transaction_id' => $transaction->id,
            'order_id' => $order->id,
            'amount' => $transaction->amount,
        ]);

        return $transaction;
    }

    /**
     * Process a refund for a completed payment transaction.
     */
    public function processRefund(Transaction $transaction, float $amount, array $data = []): Transaction
    {
        if (!$transaction->canBeRefunded()) {
            throw new \Exception('This transaction cannot be refunded');
        }

        DB::beginTransaction();
        try {
            // Create refund transaction
            $refundData = [
                'order_id' => $transaction->order_id,
                'payment_method' => $transaction->payment_method,
                'gateway' => $transaction->gateway,
                'amount' => $amount,
                'status' => 'pending',
                'notes' => $data['notes'] ?? 'Refund for transaction ' . $transaction->transaction_number,
            ];

            if (isset($data['credit_memo_id'])) {
                $refundData['credit_memo_id'] = $data['credit_memo_id'];
            }

            $refundTransaction = $this->createRefund($transaction->order, $refundData);

            // Here you would integrate with the payment gateway to process the actual refund
            // For now, we'll just mark it as completed
            // In production, this should call the gateway API

            DB::commit();
            return $refundTransaction;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process refund', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Retry a failed transaction.
     */
    public function retryTransaction(Transaction $transaction): bool
    {
        if (!$transaction->canBeRetried()) {
            throw new \Exception('This transaction cannot be retried');
        }

        DB::beginTransaction();
        try {
            // Here you would integrate with the payment gateway to retry the transaction
            // For now, we'll just update the status
            // In production, this should call the gateway API

            $transaction->update([
                'status' => 'pending',
                'processed_at' => null,
            ]);

            Log::info('Transaction retry initiated', [
                'transaction_id' => $transaction->id,
                'order_id' => $transaction->order_id,
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to retry transaction', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark transaction as completed.
     */
    public function markAsCompleted(Transaction $transaction, array $responseData = []): bool
    {
        DB::beginTransaction();
        try {
            $updateData = [
                'status' => 'completed',
                'processed_at' => now(),
            ];

            if (!empty($responseData)) {
                $updateData['response_data'] = array_merge(
                    $transaction->response_data ?? [],
                    $responseData
                );
            }

            $transaction->update($updateData);

            Log::info('Transaction marked as completed', [
                'transaction_id' => $transaction->id,
                'order_id' => $transaction->order_id,
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark transaction as completed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark transaction as failed.
     */
    public function markAsFailed(Transaction $transaction, string $reason = '', array $responseData = []): bool
    {
        DB::beginTransaction();
        try {
            $updateData = [
                'status' => 'failed',
                'notes' => $reason,
            ];

            if (!empty($responseData)) {
                $updateData['response_data'] = array_merge(
                    $transaction->response_data ?? [],
                    $responseData
                );
            }

            $transaction->update($updateData);

            Log::warning('Transaction marked as failed', [
                'transaction_id' => $transaction->id,
                'order_id' => $transaction->order_id,
                'reason' => $reason,
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark transaction as failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel a pending transaction.
     */
    public function cancelTransaction(Transaction $transaction): bool
    {
        if ($transaction->status !== 'pending') {
            throw new \Exception('Only pending transactions can be cancelled');
        }

        DB::beginTransaction();
        try {
            $transaction->update([
                'status' => 'cancelled',
            ]);

            Log::info('Transaction cancelled', [
                'transaction_id' => $transaction->id,
                'order_id' => $transaction->order_id,
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel transaction', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get transaction statistics for dashboard.
     */
    public function getStatistics(): array
    {
        return $this->repository->getStats();
    }

    /**
     * Log a transaction from payment gateway webhook.
     */
    public function logFromWebhook(array $data): Transaction
    {
        try {
            // Find order by order_id or gateway transaction id
            $order = null;
            
            if (isset($data['order_id'])) {
                $order = Order::find($data['order_id']);
            } elseif (isset($data['gateway_transaction_id'])) {
                // Check if transaction already exists
                if ($this->repository->gatewayTransactionExists($data['gateway_transaction_id'])) {
                    throw new \Exception('Transaction already exists');
                }
            }

            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Determine transaction type from webhook data
            $type = $data['type'] ?? 'payment';

            $transactionData = [
                'order_id' => $order->id,
                'transaction_number' => Transaction::generateTransactionNumber(),
                'type' => $type,
                'payment_method' => $data['payment_method'] ?? $order->payment_method,
                'gateway' => $data['gateway'],
                'gateway_transaction_id' => $data['gateway_transaction_id'] ?? null,
                'amount' => $data['amount'],
                'status' => $data['status'] ?? 'completed',
                'response_data' => $data['response_data'] ?? null,
                'notes' => $data['notes'] ?? 'Transaction from webhook',
                'processed_at' => now(),
            ];

            $transaction = $this->repository->create($transactionData);

            Log::info('Transaction logged from webhook', [
                'transaction_id' => $transaction->id,
                'order_id' => $order->id,
                'gateway' => $transaction->gateway,
            ]);

            return $transaction;
        } catch (\Exception $e) {
            Log::error('Failed to log transaction from webhook', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
