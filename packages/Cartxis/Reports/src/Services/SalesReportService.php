<?php

namespace Cartxis\Reports\Services;

use Carbon\Carbon;
use Cartxis\Reports\Repositories\SalesReportRepository;
use Cartxis\Shop\Models\Order;

class SalesReportService
{
    public function __construct(
        protected SalesReportRepository $repository,
        protected ReportCacheService $cacheService
    ) {}

    /**
     * Get complete sales report data.
     */
    public function getReportData(array $filters = []): array
    {
        [$startDate, $endDate] = $this->parseDateRange($filters);

        $cacheBuster = (string) (Order::max('updated_at') ?? 'none');
        $cacheFilters = array_merge($filters, ['cache_bust' => $cacheBuster]);

        return $this->cacheService->remember('sales', $cacheFilters, function () use ($startDate, $endDate, $filters) {
            return [
                'statistics' => $this->repository->getRevenueStatistics($startDate, $endDate, $filters),
                'revenueChart' => $this->repository->getRevenueOverTime($startDate, $endDate, $filters['group_by'] ?? 'day'),
                'ordersChart' => $this->repository->getOrdersByStatus($startDate, $endDate, $filters),
                'paymentChart' => $this->repository->getPaymentMethodsDistribution($startDate, $endDate),
                'topOrders' => $this->repository->getTopOrders($startDate, $endDate, 10),
            ];
        });
    }

    /**
     * Parse date range from filters.
     */
    protected function parseDateRange(array $filters): array
    {
        $startDate = isset($filters['start_date']) 
            ? Carbon::parse($filters['start_date'])->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();
            
        $endDate = isset($filters['end_date'])
            ? Carbon::parse($filters['end_date'])->endOfDay()
            : Carbon::now()->endOfDay();
            
        return [$startDate, $endDate];
    }

    /**
     * Clear sales report cache.
     */
    public function clearCache(array $filters = []): void
    {
        $this->cacheService->forget('sales', $filters);
    }
}
