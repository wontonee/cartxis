<?php

namespace Vortex\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Reports\Services\ProductReportService;

class ProductReportController extends Controller
{
    public function __construct(
        private ProductReportService $productReportService
    ) {}

    /**
     * Display product report.
     */
    public function index(Request $request): Response
    {
        $filters = [
            'start_date' => $request->input('start_date', now()->subDays(30)->format('Y-m-d')),
            'end_date' => $request->input('end_date', now()->format('Y-m-d')),
            'category_id' => $request->input('category_id'),
            'low_stock_threshold' => $request->input('low_stock_threshold', 10),
        ];

        $reportData = $this->productReportService->generateReport($filters);

        return Inertia::render('Admin/Reports/Products/Index', [
            'statistics' => $reportData['statistics'],
            'bestSellers' => $reportData['best_sellers'],
            'lowStock' => $reportData['low_stock'],
            'outOfStock' => $reportData['out_of_stock'],
            'categoryPerformance' => $reportData['category_performance'],
            'slowMoving' => $reportData['slow_moving'],
            'bestSellersChart' => $reportData['charts']['best_sellers_chart'],
            'categoryChart' => $reportData['charts']['category_chart'],
            'salesTrendChart' => $reportData['charts']['sales_trend_chart'],
            'filters' => $filters,
        ]);
    }

    /**
     * Export product report.
     */
    public function export(Request $request)
    {
        return response()->json(['message' => 'Export feature coming soon']);
    }
}
