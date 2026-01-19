<?php

namespace Cartxis\Sales\Repositories;

use Cartxis\Sales\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionRepository
{
    /**
     * Get paginated transactions with filters.
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Transaction::with(['order', 'invoice', 'creditMemo']);

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('gateway_transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_number', 'like', "%{$search}%");
                    });
            });
        }

        // Type filter
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Gateway filter
        if (!empty($filters['gateway'])) {
            $query->where('gateway', $filters['gateway']);
        }

        // Payment method filter
        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Amount range filter
        if (!empty($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (!empty($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }

        // Order ID filter
        if (!empty($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Find a transaction by ID with relationships.
     */
    public function find(int $id): ?Transaction
    {
        return Transaction::with(['order', 'invoice', 'creditMemo'])->find($id);
    }

    /**
     * Get transactions for a specific order.
     */
    public function getForOrder(int $orderId): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get transactions statistics.
     */
    public function getStats(): array
    {
        return [
            'total' => Transaction::count(),
            'completed' => Transaction::where('status', 'completed')->count(),
            'pending' => Transaction::where('status', 'pending')->count(),
            'failed' => Transaction::where('status', 'failed')->count(),
            'total_amount' => Transaction::where('status', 'completed')->sum('amount'),
            'payment_count' => Transaction::where('type', 'payment')->where('status', 'completed')->count(),
            'refund_count' => Transaction::where('type', 'refund')->where('status', 'completed')->count(),
            'payment_amount' => Transaction::where('type', 'payment')->where('status', 'completed')->sum('amount'),
            'refund_amount' => Transaction::where('type', 'refund')->where('status', 'completed')->sum('amount'),
        ];
    }

    /**
     * Get transactions by type.
     */
    public function getByType(string $type, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::with(['order'])
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent transactions.
     */
    public function getRecent(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::with(['order'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get failed transactions.
     */
    public function getFailed(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::with(['order'])
            ->where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get transactions by gateway.
     */
    public function getByGateway(string $gateway, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::with(['order'])
            ->where('gateway', $gateway)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get total transaction amount for a date range.
     */
    public function getTotalAmountForDateRange(string $startDate, string $endDate, string $type = null): float
    {
        $query = Transaction::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->sum('amount');
    }

    /**
     * Get transaction count by status.
     */
    public function getCountByStatus(): array
    {
        return Transaction::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get transaction count by type.
     */
    public function getCountByType(): array
    {
        return Transaction::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    /**
     * Get transaction count by gateway.
     */
    public function getCountByGateway(): array
    {
        return Transaction::select('gateway', DB::raw('count(*) as count'))
            ->groupBy('gateway')
            ->pluck('count', 'gateway')
            ->toArray();
    }

    /**
     * Create a new transaction.
     */
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * Update a transaction.
     */
    public function update(int $id, array $data): bool
    {
        $transaction = Transaction::find($id);
        
        if (!$transaction) {
            return false;
        }

        return $transaction->update($data);
    }

    /**
     * Delete a transaction.
     */
    public function delete(int $id): bool
    {
        $transaction = Transaction::find($id);
        
        if (!$transaction) {
            return false;
        }

        return $transaction->delete();
    }

    /**
     * Check if a gateway transaction ID already exists.
     */
    public function gatewayTransactionExists(string $gatewayTransactionId): bool
    {
        return Transaction::where('gateway_transaction_id', $gatewayTransactionId)->exists();
    }

    /**
     * Get transactions for export.
     */
    public function getForExport(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Transaction::with(['order', 'invoice', 'creditMemo']);

        // Apply same filters as paginate
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('gateway_transaction_id', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['gateway'])) {
            $query->where('gateway', $filters['gateway']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
