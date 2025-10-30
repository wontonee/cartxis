<?php

namespace Vortex\Sales\Repositories;

use Vortex\Shop\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    /**
     * Get orders for admin with filters and pagination.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAdminOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::with(['user', 'items'])
            ->select('orders.*');

        // Search filter (order number, customer email, customer name)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_email', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Payment status filter
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        // Payment method filter
        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        // Date range filter (from)
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        // Date range filter (to)
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Total amount range (min)
        if (!empty($filters['total_min'])) {
            $query->where('total', '>=', $filters['total_min']);
        }

        // Total amount range (max)
        if (!empty($filters['total_max'])) {
            $query->where('total', '<=', $filters['total_max']);
        }

        // Customer filter (user_id)
        if (!empty($filters['customer_id'])) {
            $query->where('user_id', $filters['customer_id']);
        }

        // Shipping method filter
        if (!empty($filters['shipping_method'])) {
            $query->where('shipping_method', $filters['shipping_method']);
        }

        // Sort by
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $allowedSorts = ['created_at', 'total', 'status', 'payment_status', 'order_number'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get order statistics for admin dashboard.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        return [
            // Total orders
            'total_orders' => Order::count(),
            
            // Today's orders
            'today_orders' => Order::where('created_at', '>=', $today)->count(),
            
            // This month's orders
            'month_orders' => Order::where('created_at', '>=', $thisMonth)->count(),
            
            // Last month's orders
            'last_month_orders' => Order::whereBetween('created_at', [
                $lastMonth,
                $lastMonth->copy()->endOfMonth()
            ])->count(),

            // Total revenue
            'total_revenue' => Order::where('payment_status', Order::PAYMENT_PAID)
                ->sum('total'),
            
            // Today's revenue
            'today_revenue' => Order::where('payment_status', Order::PAYMENT_PAID)
                ->where('created_at', '>=', $today)
                ->sum('total'),
            
            // This month's revenue
            'month_revenue' => Order::where('payment_status', Order::PAYMENT_PAID)
                ->where('created_at', '>=', $thisMonth)
                ->sum('total'),
            
            // Last month's revenue
            'last_month_revenue' => Order::where('payment_status', Order::PAYMENT_PAID)
                ->whereBetween('created_at', [
                    $lastMonth,
                    $lastMonth->copy()->endOfMonth()
                ])
                ->sum('total'),

            // Average order value
            'average_order_value' => Order::where('payment_status', Order::PAYMENT_PAID)
                ->avg('total'),

            // Orders by status
            'orders_by_status' => Order::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),

            // Orders by payment status
            'orders_by_payment_status' => Order::select('payment_status', DB::raw('count(*) as count'))
                ->groupBy('payment_status')
                ->pluck('count', 'payment_status')
                ->toArray(),

            // Pending orders (requires action)
            'pending_orders' => Order::where('status', Order::STATUS_PENDING)->count(),
            
            // Processing orders
            'processing_orders' => Order::where('status', Order::STATUS_PROCESSING)->count(),
            
            // Failed payments (requires attention)
            'failed_payments' => Order::where('payment_status', Order::PAYMENT_FAILED)->count(),

            // Recent orders (last 10)
            'recent_orders' => Order::with(['user', 'items'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),

            // Top customers (by order count)
            'top_customers_by_orders' => Order::select('user_id', DB::raw('count(*) as order_count'))
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->orderBy('order_count', 'desc')
                ->limit(10)
                ->with('user')
                ->get(),

            // Top customers (by revenue)
            'top_customers_by_revenue' => Order::select('user_id', DB::raw('sum(total) as total_spent'))
                ->whereNotNull('user_id')
                ->where('payment_status', Order::PAYMENT_PAID)
                ->groupBy('user_id')
                ->orderBy('total_spent', 'desc')
                ->limit(10)
                ->with('user')
                ->get(),

            // Daily sales for last 30 days
            'daily_sales' => Order::where('created_at', '>=', now()->subDays(30))
                ->where('payment_status', Order::PAYMENT_PAID)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('count(*) as orders'),
                    DB::raw('sum(total) as revenue')
                )
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get(),
        ];
    }

    /**
     * Get orders for export (without pagination).
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOrdersForExport(array $filters = [])
    {
        $query = Order::with(['user', 'items', 'addresses'])
            ->select('orders.*');

        // Apply same filters as getAdminOrders
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_email', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get order by ID with all relationships.
     *
     * @param int $orderId
     * @return Order|null
     */
    public function getOrderWithRelations(int $orderId): ?Order
    {
        return Order::with([
            'user',
            'items.product',
            'addresses',
            'histories.adminUser',
            'shipments.shipmentItems.orderItem.product'
        ])->find($orderId);
    }
}
