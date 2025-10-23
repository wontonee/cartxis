<?php

namespace Vortex\Shop\Repositories;

use Vortex\Shop\Contracts\OrderRepositoryInterface;
use Vortex\Shop\Models\Order;

class OrderRepository extends ShopRepository implements OrderRepositoryInterface
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Order::class;
    }

    /**
     * Find an order by order number.
     *
     * @param  string  $orderNumber
     * @return \Vortex\Shop\Models\Order|null
     */
    public function findByOrderNumber(string $orderNumber)
    {
        return $this->model->where('order_number', $orderNumber)->first();
    }

    /**
     * Get orders for a specific user.
     *
     * @param  int  $userId
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByUser(int $userId, int $perPage = 10)
    {
        return $this->model
            ->where('user_id', $userId)
            ->with(['items.product', 'addresses'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders with a specific status.
     *
     * @param  string  $status
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByStatus(string $status, int $perPage = 10)
    {
        return $this->model
            ->where('status', $status)
            ->with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Update order status.
     *
     * @param  int  $orderId
     * @param  string  $status
     * @return bool
     */
    public function updateStatus(int $orderId, string $status): bool
    {
        $order = $this->find($orderId);
        
        if (!$order) {
            return false;
        }

        return $order->update(['status' => $status]);
    }

    /**
     * Update payment status.
     *
     * @param  int  $orderId
     * @param  string  $status
     * @return bool
     */
    public function updatePaymentStatus(int $orderId, string $status): bool
    {
        $order = $this->find($orderId);
        
        if (!$order) {
            return false;
        }

        return $order->update(['payment_status' => $status]);
    }

    /**
     * Get order with all relationships (items, addresses, user).
     *
     * @param  int  $orderId
     * @return \Vortex\Shop\Models\Order|null
     */
    public function getWithRelations(int $orderId)
    {
        return $this->model
            ->with([
                'user',
                'items.product.mainImage',
                'addresses',
            ])
            ->find($orderId);
    }

    /**
     * Get recent orders.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecent(int $limit = 10)
    {
        return $this->model
            ->with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get total order count for a user.
     *
     * @param  int  $userId
     * @return int
     */
    public function getUserOrderCount(int $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->count();
    }

    /**
     * Get total revenue for a user.
     *
     * @param  int  $userId
     * @return float
     */
    public function getUserTotalRevenue(int $userId): float
    {
        return (float) $this->model
            ->where('user_id', $userId)
            ->where('payment_status', Order::PAYMENT_PAID)
            ->sum('total');
    }

    /**
     * Search orders by order number, email, or name.
     *
     * @param  string  $query
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search(string $query, int $perPage = 10)
    {
        return $this->model
            ->where(function ($q) use ($query) {
                $q->where('order_number', 'LIKE', "%{$query}%")
                  ->orWhere('customer_email', 'LIKE', "%{$query}%")
                  ->orWhereHas('user', function ($userQuery) use ($query) {
                      $userQuery->where('name', 'LIKE', "%{$query}%")
                               ->orWhere('email', 'LIKE', "%{$query}%");
                  });
            })
            ->with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders within a date range.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByDateRange(string $startDate, string $endDate, int $perPage = 10)
    {
        return $this->model
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Cancel an order.
     *
     * @param  int  $orderId
     * @return bool
     */
    public function cancel(int $orderId): bool
    {
        $order = $this->find($orderId);
        
        if (!$order || !$order->canBeCancelled()) {
            return false;
        }

        return $order->update(['status' => Order::STATUS_CANCELLED]);
    }

    /**
     * Mark order as paid.
     *
     * @param  int  $orderId
     * @return bool
     */
    public function markAsPaid(int $orderId): bool
    {
        $order = $this->find($orderId);
        
        if (!$order) {
            return false;
        }

        return $order->update([
            'payment_status' => Order::PAYMENT_PAID,
            'status' => Order::STATUS_PROCESSING,
        ]);
    }
}
