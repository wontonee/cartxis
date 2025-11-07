<?php

namespace Vortex\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Reports\Services\SalesReportService;
use Vortex\Reports\Resources\SalesReportResource;

class SalesReportController extends Controller
{
    public function __construct(
        protected SalesReportService $service
    ) {}

    /**
     * Display sales report.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['start_date', 'end_date', 'status', 'payment_method', 'group_by']);
        
        $reportData = $this->service->getReportData($filters);
        
        return Inertia::render('Admin/Reports/Sales/Index', [
            'statistics' => $reportData['statistics'],
            'revenueChart' => $reportData['revenueChart'],
            'ordersChart' => $reportData['ordersChart'],
            'paymentChart' => $reportData['paymentChart'],
            'topOrders' => $reportData['topOrders']->map(function ($order) {
                return [
                    'id' => $order->id,
                    'increment_id' => $order->increment_id ?? $order->id,
                    'customer_name' => $order->customer?->name ?? $order->customer_name ?? 'Guest',
                    'total' => $order->total,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('M d, Y'),
                ];
            }),
            'filters' => [
                'start_date' => $request->input('start_date', now()->subDays(30)->format('Y-m-d')),
                'end_date' => $request->input('end_date', now()->format('Y-m-d')),
                'status' => $request->input('status'),
                'payment_method' => $request->input('payment_method'),
            ],
        ]);
    }

    /**
     * Export sales report.
     */
    public function export(Request $request)
    {
        // TODO: Implement export functionality (PDF/Excel)
        return response()->json(['message' => 'Export feature coming soon']);
    }
}
