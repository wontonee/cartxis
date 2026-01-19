<?php

declare(strict_types=1);

namespace Cartxis\Reports\Services;

use Cartxis\Reports\Repositories\CustomerReportRepository;

class CustomerReportService
{
    public function __construct(
        private CustomerReportRepository $repository,
        private ReportCacheService $cacheService
    ) {}

    /**
     * Generate customer report
     */
    public function generateReport(array $filters): array
    {
        return $this->cacheService->remember('customers', $filters, function () use ($filters) {
            $startDate = $filters['start_date'];
            $endDate = $filters['end_date'];

            $statistics = $this->repository->getCustomerStatistics($startDate, $endDate);
            $topCustomers = $this->repository->getTopCustomers($startDate, $endDate);
            $acquisitionTrend = $this->repository->getCustomerAcquisitionTrend($startDate, $endDate);
            $rfmSegmentation = $this->repository->getRFMSegmentation($startDate, $endDate);
            $geographicDistribution = $this->repository->getGeographicDistribution($startDate, $endDate);
            $lifetimeValueDistribution = $this->repository->getLifetimeValueDistribution($startDate, $endDate);

            $charts = $this->prepareChartData($acquisitionTrend, $rfmSegmentation, $geographicDistribution, $lifetimeValueDistribution);

            return compact(
                'statistics',
                'topCustomers',
                'acquisitionTrend',
                'rfmSegmentation',
                'geographicDistribution',
                'lifetimeValueDistribution',
                'charts'
            );
        });
    }

    /**
     * Prepare chart data
     */
    private function prepareChartData(
        array $acquisitionTrend,
        array $rfmSegmentation,
        array $geographicDistribution,
        array $lifetimeValueDistribution
    ): array {
        // Acquisition trend chart (Line)
        $acquisitionChart = [
            'labels' => array_column($acquisitionTrend, 'date'),
            'datasets' => [
                [
                    'label' => 'New Customers',
                    'data' => array_column($acquisitionTrend, 'new_customers'),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ]
            ]
        ];

        // RFM segmentation chart (Doughnut)
        $segmentCounts = [];
        foreach ($rfmSegmentation as $customer) {
            $segment = $customer['segment'];
            if (!isset($segmentCounts[$segment])) {
                $segmentCounts[$segment] = 0;
            }
            $segmentCounts[$segment]++;
        }

        $rfmChart = [
            'labels' => array_keys($segmentCounts),
            'datasets' => [
                [
                    'label' => 'Customers',
                    'data' => array_values($segmentCounts),
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                        'rgb(168, 85, 247)',
                        'rgb(249, 115, 22)',
                        'rgb(236, 72, 153)',
                        'rgb(234, 179, 8)',
                        'rgb(239, 68, 68)',
                        'rgb(156, 163, 175)',
                    ],
                ]
            ]
        ];

        // Geographic distribution chart (Bar)
        $geographicChart = [
            'labels' => array_column($geographicDistribution, 'country'),
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => array_column($geographicDistribution, 'total_revenue'),
                    'backgroundColor' => 'rgb(59, 130, 246)',
                ]
            ]
        ];

        // Lifetime value distribution chart (Bar)
        $ltvChart = [
            'labels' => array_keys($lifetimeValueDistribution),
            'datasets' => [
                [
                    'label' => 'Number of Customers',
                    'data' => array_values($lifetimeValueDistribution),
                    'backgroundColor' => 'rgb(34, 197, 94)',
                ]
            ]
        ];

        return [
            'acquisition_chart' => $acquisitionChart,
            'rfm_chart' => $rfmChart,
            'geographic_chart' => $geographicChart,
            'ltv_chart' => $ltvChart,
        ];
    }
}
