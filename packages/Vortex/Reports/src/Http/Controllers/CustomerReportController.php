<?php

namespace Vortex\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Reports\Services\CustomerReportService;

class CustomerReportController extends Controller
{
    public function __construct(
        private CustomerReportService $customerReportService
    ) {}

    /**
     * Display customer report.
     */
    public function index(Request $request): Response
    {
        $filters = [
            'start_date' => $request->input('start_date', now()->subDays(30)->format('Y-m-d')),
            'end_date' => $request->input('end_date', now()->format('Y-m-d')),
        ];

        $data = $this->customerReportService->generateReport($filters);

        return Inertia::render('Admin/Reports/Customers/Index', [
            'statistics' => $data['statistics'],
            'topCustomers' => $data['topCustomers'],
            'acquisitionTrend' => $data['acquisitionTrend'],
            'rfmSegmentation' => $data['rfmSegmentation'],
            'geographicDistribution' => $data['geographicDistribution'],
            'lifetimeValueDistribution' => $data['lifetimeValueDistribution'],
            'acquisitionChart' => $data['charts']['acquisition_chart'],
            'rfmChart' => $data['charts']['rfm_chart'],
            'geographicChart' => $data['charts']['geographic_chart'],
            'ltvChart' => $data['charts']['ltv_chart'],
            'filters' => $filters,
        ]);
    }

    /**
     * Export customer report.
     */
    public function export(Request $request)
    {
        return response()->json(['message' => 'Export feature coming soon']);
    }
}

