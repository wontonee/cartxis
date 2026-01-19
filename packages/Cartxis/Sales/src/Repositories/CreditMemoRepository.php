<?php

namespace Cartxis\Sales\Repositories;

use Cartxis\Sales\Models\CreditMemo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CreditMemoRepository
{
    /**
     * Get paginated credit memos with filters
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = CreditMemo::with(['order.user', 'invoice', 'items', 'creator']);

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('credit_memo_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($orderQuery) use ($search) {
                      $orderQuery->where('order_number', 'like', "%{$search}%")
                          ->orWhere('customer_email', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Refund status filter
        if (!empty($filters['refund_status'])) {
            $query->where('refund_status', $filters['refund_status']);
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Amount range filter
        if (!empty($filters['min_amount'])) {
            $query->where('grand_total', '>=', $filters['min_amount']);
        }
        if (!empty($filters['max_amount'])) {
            $query->where('grand_total', '<=', $filters['max_amount']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Find credit memo by ID
     */
    public function find(int $id): ?CreditMemo
    {
        return CreditMemo::with(['order.user', 'order.items', 'invoice', 'items.product', 'creator'])
            ->find($id);
    }

    /**
     * Find credit memo by number
     */
    public function findByNumber(string $number): ?CreditMemo
    {
        return CreditMemo::with(['order.user', 'order.items', 'invoice', 'items.product', 'creator'])
            ->where('credit_memo_number', $number)
            ->first();
    }

    /**
     * Create new credit memo
     */
    public function create(array $data): CreditMemo
    {
        return CreditMemo::create($data);
    }

    /**
     * Update credit memo
     */
    public function update(int $id, array $data): bool
    {
        $creditMemo = $this->find($id);
        
        if (!$creditMemo) {
            return false;
        }

        return $creditMemo->update($data);
    }

    /**
     * Delete credit memo
     */
    public function delete(int $id): bool
    {
        $creditMemo = $this->find($id);
        
        if (!$creditMemo || !$creditMemo->canBeDeleted()) {
            return false;
        }

        return $creditMemo->delete();
    }

    /**
     * Get credit memos for specific order
     */
    public function getForOrder(int $orderId): Collection
    {
        return CreditMemo::with(['items', 'creator'])
            ->where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get total refunded amount for order
     */
    public function getTotalRefundedForOrder(int $orderId): float
    {
        return CreditMemo::where('order_id', $orderId)
            ->where('status', 'complete')
            ->sum('grand_total');
    }

    /**
     * Get credit memo statistics
     */
    public function getStats(): array
    {
        return [
            'total' => CreditMemo::count(),
            'pending' => CreditMemo::where('status', 'pending')->count(),
            'refunded' => CreditMemo::where('status', 'complete')->count(),
            'cancelled' => CreditMemo::where('status', 'cancelled')->count(),
            'total_amount' => CreditMemo::where('status', 'complete')->sum('grand_total') ?? 0,
            'pending_amount' => CreditMemo::where('status', 'pending')->sum('grand_total') ?? 0,
        ];
    }

    /**
     * Bulk delete credit memos
     */
    public function bulkDelete(array $ids): int
    {
        return CreditMemo::whereIn('id', $ids)
            ->where('status', 'pending')
            ->where('refund_status', '!=', 'processed')
            ->delete();
    }
}
