<?php

namespace Vortex\Shop\Contracts;

interface OrderRepositoryInterface extends ShopRepositoryInterface
{
    /**
     * Find an order by order number.
     *
     * @param  string  $orderNumber
     * @return \Vortex\Shop\Models\Order|null
     */
    public function findByOrderNumber(string $orderNumber);

    /**
     * Get orders for a specific user.
     *
     * @param  int  $userId
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByUser(int $userId, int $perPage = 10);

    /**
     * Get orders with a specific status.
     *
     * @param  string  $status
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByStatus(string $status, int $perPage = 10);

    /**
     * Update order status.
     *
     * @param  int  $orderId
     * @param  string  $status
     * @return bool
     */
    public function updateStatus(int $orderId, string $status): bool;

    /**
     * Update payment status.
     *
     * @param  int  $orderId
     * @param  string  $status
     * @return bool
     */
    public function updatePaymentStatus(int $orderId, string $status): bool;

    /**
     * Get order with all relationships (items, addresses, user).
     *
     * @param  int  $orderId
     * @return \Vortex\Shop\Models\Order|null
     */
    public function getWithRelations(int $orderId);

    /**
     * Get recent orders.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecent(int $limit = 10);

    /**
     * Get total order count for a user.
     *
     * @param  int  $userId
     * @return int
     */
    public function getUserOrderCount(int $userId): int;

    /**
     * Get total revenue for a user.
     *
     * @param  int  $userId
     * @return float
     */
    public function getUserTotalRevenue(int $userId): float;

    /**
     * Search orders by order number, email, or name.
     *
     * @param  string  $query
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search(string $query, int $perPage = 10);

    /**
     * Get orders within a date range.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByDateRange(string $startDate, string $endDate, int $perPage = 10);

    /**
     * Cancel an order.
     *
     * @param  int  $orderId
     * @return bool
     */
    public function cancel(int $orderId): bool;

    /**
     * Mark order as paid.
     *
     * @param  int  $orderId
     * @return bool
     */
    public function markAsPaid(int $orderId): bool;
}
