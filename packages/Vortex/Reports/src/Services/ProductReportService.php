<?php

namespace Vortex\Reports\Services;

use Vortex\Reports\Repositories\ProductReportRepository;

class ProductReportService
{
    public function __construct(
        private ProductReportRepository $repository,
        private ReportCacheService $cacheService
    ) {}

    /**
     * Generate product report data
     */
    public function generateReport(array $filters): array
    {
        return $this->cacheService->remember('products', $filters, function() use ($filters) {
            $startDate = $filters['start_date'] ?? now()->subDays(30)->format('Y-m-d');
            $endDate = $filters['end_date'] ?? now()->format('Y-m-d');
            $lowStockThreshold = $filters['low_stock_threshold'] ?? 10;

            // Get all data
            $statistics = $this->repository->getProductStatistics($startDate, $endDate);
            $bestSellers = $this->repository->getBestSellers($startDate, $endDate);
            $lowStock = $this->repository->getLowStockProducts($lowStockThreshold);
            $outOfStock = $this->repository->getOutOfStockProducts();
            $categoryPerformance = $this->repository->getCategoryPerformance($startDate, $endDate);
            $slowMoving = $this->repository->getSlowMovingProducts($startDate, $endDate);
            $salesTrend = $this->repository->getProductSalesTrend($startDate, $endDate);

            // Prepare chart data
            $chartData = $this->prepareChartData(
                $bestSellers,
                $categoryPerformance,
                $salesTrend
            );

            return [
                'statistics' => $statistics,
                'best_sellers' => $bestSellers,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
                'category_performance' => $categoryPerformance,
                'slow_moving' => $slowMoving,
                'charts' => $chartData,
            ];
        });
    }

    /**
     * Prepare chart data for frontend
     */
    private function prepareChartData(
        array $bestSellers,
        array $categoryPerformance,
        array $salesTrend
    ): array {
        return [
            // Best Sellers Bar Chart
            'best_sellers_chart' => [
                'labels' => array_map(fn($item) => $item['name'], array_slice($bestSellers, 0, 10)),
                'datasets' => [
                    [
                        'label' => 'Quantity Sold',
                        'data' => array_map(fn($item) => $item['total_quantity'], array_slice($bestSellers, 0, 10)),
                        'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    ]
                ]
            ],

            // Category Performance Pie Chart
            'category_chart' => [
                'labels' => array_map(fn($item) => $item['category_name'], $categoryPerformance),
                'datasets' => [
                    [
                        'label' => 'Revenue by Category',
                        'data' => array_map(fn($item) => $item['total_revenue'], $categoryPerformance),
                        'backgroundColor' => [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                        ],
                    ]
                ]
            ],

            // Sales Trend Line Chart
            'sales_trend_chart' => [
                'labels' => array_map(function($item) {
                    return date('M d', strtotime($item['date']));
                }, $salesTrend),
                'datasets' => [
                    [
                        'label' => 'Revenue',
                        'data' => array_map(fn($item) => $item['revenue'], $salesTrend),
                        'borderColor' => 'rgba(59, 130, 246, 1)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Quantity',
                        'data' => array_map(fn($item) => $item['quantity'], $salesTrend),
                        'borderColor' => 'rgba(16, 185, 129, 1)',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                    ]
                ]
            ],
        ];
    }
}
