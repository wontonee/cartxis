<?php

namespace Vortex\Sales\Repositories;

use Vortex\Sales\Models\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InvoiceRepository
{
    /**
     * Get paginated invoices
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Invoice::with('order.user');

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($orderQuery) use ($search) {
                      $orderQuery->where('order_number', 'like', "%{$search}%")
                                ->orWhere('customer_email', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('issue_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('issue_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['min_amount'])) {
            $query->where('total', '>=', $filters['min_amount']);
        }

        if (!empty($filters['max_amount'])) {
            $query->where('total', '<=', $filters['max_amount']);
        }

        // Apply sorting
        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Find invoice by ID
     */
    public function find(int $id): ?Invoice
    {
        return Invoice::with('order.user', 'order.items', 'order.addresses')->find($id);
    }

    /**
     * Find invoice by invoice number
     */
    public function findByInvoiceNumber(string $invoiceNumber): ?Invoice
    {
        return Invoice::with('order')->where('invoice_number', $invoiceNumber)->first();
    }

    /**
     * Get invoices for specific order
     */
    public function getByOrder(int $orderId): Collection
    {
        return Invoice::where('order_id', $orderId)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Create new invoice
     */
    public function create(array $data): Invoice
    {
        // Generate invoice number if not provided
        if (!isset($data['invoice_number'])) {
            $data['invoice_number'] = Invoice::generateInvoiceNumber();
        }

        // Set issue date if not provided
        if (!isset($data['issue_date'])) {
            $data['issue_date'] = now();
        }

        return Invoice::create($data);
    }

    /**
     * Update invoice
     */
    public function update(int $id, array $data): bool
    {
        $invoice = Invoice::find($id);
        
        if (!$invoice) {
            return false;
        }

        return $invoice->update($data);
    }

    /**
     * Delete invoice
     */
    public function delete(int $id): bool
    {
        $invoice = Invoice::find($id);
        
        if (!$invoice) {
            return false;
        }

        return $invoice->delete();
    }

    /**
     * Update invoice status
     */
    public function updateStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Get invoices by status
     */
    public function getByStatus(string $status): Collection
    {
        return Invoice::withStatus($status)->with('order')->get();
    }

    /**
     * Get invoices within date range
     */
    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return Invoice::dateRange($startDate, $endDate)->with('order')->get();
    }

    /**
     * Get recent invoices
     */
    public function getRecent(int $limit = 10): Collection
    {
        return Invoice::with('order')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get pending invoices count
     */
    public function getPendingCount(): int
    {
        return Invoice::where('status', 'pending')->count();
    }

    /**
     * Get total invoice amount by status
     */
    public function getTotalByStatus(string $status): float
    {
        return Invoice::where('status', $status)->sum('total');
    }
}
