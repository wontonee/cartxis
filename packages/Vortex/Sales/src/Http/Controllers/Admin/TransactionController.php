<?php

namespace Vortex\Sales\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Vortex\Sales\Models\Transaction;
use Vortex\Sales\Services\TransactionService;
use Vortex\Sales\Repositories\TransactionRepository;
use Illuminate\Support\Facades\Response;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService,
        protected TransactionRepository $transactionRepository
    ) {}

    /**
     * Display a listing of transactions.
     */
    public function index(Request $request): InertiaResponse
    {
        $filters = $request->only([
            'search',
            'type',
            'status',
            'gateway',
            'payment_method',
            'date_from',
            'date_to',
            'amount_min',
            'amount_max',
            'order_id',
            'sort_by',
            'sort_order'
        ]);

        $transactions = $this->transactionRepository->paginate(
            $filters,
            $request->input('per_page', 15)
        );

        $statistics = $this->transactionRepository->getStats();

        return Inertia::render('Admin/Sales/Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $filters,
            'statistics' => $statistics,
            'types' => $this->getTransactionTypes(),
            'statuses' => $this->getTransactionStatuses(),
            'gateways' => $this->getGateways(),
        ]);
    }

    /**
     * Display the specified transaction.
     */
    public function show(int $id): InertiaResponse
    {
        $transaction = $this->transactionRepository->find($id);

        if (!$transaction) {
            abort(404);
        }

        // Get other transactions for the same order
        $relatedTransactions = $this->transactionRepository->getForOrder($transaction->order_id)
            ->where('id', '!=', $transaction->id)
            ->values();

        return Inertia::render('Admin/Sales/Transactions/Show', [
            'transaction' => $transaction,
            'relatedTransactions' => $relatedTransactions,
        ]);
    }

    /**
     * Process a refund for a transaction.
     */
    public function refund(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
            'credit_memo_id' => 'nullable|exists:credit_memos,id',
        ]);

        $transaction = $this->transactionRepository->find($id);

        if (!$transaction) {
            return redirect()
                ->route('admin.sales.transactions.index')
                ->with('error', 'Transaction not found');
        }

        if (!$transaction->canBeRefunded()) {
            return redirect()
                ->route('admin.sales.transactions.show', $id)
                ->with('error', 'This transaction cannot be refunded');
        }

        if ($validated['amount'] > $transaction->amount) {
            return redirect()
                ->route('admin.sales.transactions.show', $id)
                ->with('error', 'Refund amount cannot exceed transaction amount');
        }

        try {
            $refundTransaction = $this->transactionService->processRefund(
                $transaction,
                $validated['amount'],
                [
                    'notes' => $validated['notes'] ?? null,
                    'credit_memo_id' => $validated['credit_memo_id'] ?? null,
                ]
            );

            return redirect()
                ->route('admin.sales.transactions.show', $refundTransaction->id)
                ->with('success', 'Refund transaction created successfully');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.sales.transactions.show', $id)
                ->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }

    /**
     * Retry a failed transaction.
     */
    public function retry(int $id): RedirectResponse
    {
        $transaction = $this->transactionRepository->find($id);

        if (!$transaction) {
            return redirect()
                ->route('admin.sales.transactions.index')
                ->with('error', 'Transaction not found');
        }

        if (!$transaction->canBeRetried()) {
            return redirect()
                ->route('admin.sales.transactions.show', $id)
                ->with('error', 'This transaction cannot be retried');
        }

        try {
            $this->transactionService->retryTransaction($transaction);

            return redirect()
                ->route('admin.sales.transactions.show', $id)
                ->with('success', 'Transaction retry initiated successfully');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.sales.transactions.show', $id)
                ->with('error', 'Failed to retry transaction: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a pending transaction.
     */
    public function cancel(int $id): RedirectResponse
    {
        $transaction = $this->transactionRepository->find($id);

        if (!$transaction) {
            return redirect()
                ->route('admin.sales.transactions.index')
                ->with('error', 'Transaction not found');
        }

        if ($transaction->status !== 'pending') {
            return redirect()
                ->route('admin.sales.transactions.show', $id)
                ->with('error', 'Only pending transactions can be cancelled');
        }

        try {
            $this->transactionService->cancelTransaction($transaction);

            return redirect()
                ->route('admin.sales.transactions.show', $id)
                ->with('success', 'Transaction cancelled successfully');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.sales.transactions.show', $id)
                ->with('error', 'Failed to cancel transaction: ' . $e->getMessage());
        }
    }

    /**
     * Export transactions to CSV.
     */
    public function export(Request $request)
    {
        $filters = $request->only([
            'search',
            'type',
            'status',
            'gateway',
            'payment_method',
            'date_from',
            'date_to',
            'amount_min',
            'amount_max',
        ]);

        $transactions = $this->transactionRepository->getForExport($filters);

        $filename = 'transactions_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'Transaction #',
                'Order #',
                'Type',
                'Payment Method',
                'Gateway',
                'Gateway Transaction ID',
                'Amount',
                'Status',
                'Date',
                'Processed At',
            ]);

            // Add data rows
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_number,
                    $transaction->order?->order_number ?? 'N/A',
                    ucfirst($transaction->type),
                    $transaction->payment_method,
                    ucfirst($transaction->gateway),
                    $transaction->gateway_transaction_id ?? 'N/A',
                    $transaction->amount,
                    ucfirst($transaction->status),
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->processed_at?->format('Y-m-d H:i:s') ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Get available transaction types.
     */
    private function getTransactionTypes(): array
    {
        return [
            ['value' => 'payment', 'label' => 'Payment'],
            ['value' => 'refund', 'label' => 'Refund'],
            ['value' => 'authorization', 'label' => 'Authorization'],
            ['value' => 'capture', 'label' => 'Capture'],
        ];
    }

    /**
     * Get available transaction statuses.
     */
    private function getTransactionStatuses(): array
    {
        return [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'completed', 'label' => 'Completed'],
            ['value' => 'failed', 'label' => 'Failed'],
            ['value' => 'cancelled', 'label' => 'Cancelled'],
        ];
    }

    /**
     * Get available payment gateways.
     */
    private function getGateways(): array
    {
        return [
            ['value' => 'stripe', 'label' => 'Stripe'],
            ['value' => 'paypal', 'label' => 'PayPal'],
            ['value' => 'razorpay', 'label' => 'Razorpay'],
            ['value' => 'cod', 'label' => 'Cash on Delivery'],
            ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
        ];
    }
}
