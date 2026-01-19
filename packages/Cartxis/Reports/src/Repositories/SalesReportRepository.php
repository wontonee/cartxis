<?php

namespace Cartxis\Reports\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Cartxis\Shop\Models\Order;

class SalesReportRepository
{
    /**
     * Get revenue statistics for date range.
     */
    public function getRevenueStatistics(Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $query = $this->buildBaseQuery($startDate, $endDate, $filters);
        
        // Current period statistics
        $currentStats = $query->selectRaw('
            COUNT(*) as order_count,
            SUM(CASE WHEN status = "completed" THEN total ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = "refunded" THEN total ELSE 0 END) as refunded_amount,
            AVG(CASE WHEN status = "completed" THEN total ELSE NULL END) as avg_order_value
        ')->first();
        
        // Previous period for comparison
        $periodDiff = $startDate->diffInDays($endDate);
        $previousStart = $startDate->copy()->subDays($periodDiff);
        $previousEnd = $startDate->copy()->subDay();
        
        $previousRevenue = Order::whereBetween('created_at', [$previousStart, $previousEnd])
            ->where('status', 'completed')
            ->sum('total');
        
        // Calculate growth
        $growthPercentage = $previousRevenue > 0 
            ? (($currentStats->total_revenue - $previousRevenue) / $previousRevenue) * 100 
            : 0;
        
        return [
            'total_revenue' => (float) ($currentStats->total_revenue ?? 0),
            'order_count' => (int) ($currentStats->order_count ?? 0),
            'avg_order_value' => (float) ($currentStats->avg_order_value ?? 0),
            'refunded_amount' => (float) ($currentStats->refunded_amount ?? 0),
            'net_revenue' => (float) (($currentStats->total_revenue ?? 0) - ($currentStats->refunded_amount ?? 0)),
            'growth_percentage' => round($growthPercentage, 2),
            'previous_period_revenue' => (float) $previousRevenue,
        ];
    }

    /**
     * Get revenue over time for chart.
     */
    public function getRevenueOverTime(Carbon $startDate, Carbon $endDate, string $groupBy = 'day'): array
    {
        $dateFormat = match($groupBy) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };
        
        $results = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->selectRaw("
                DATE_FORMAT(created_at, '{$dateFormat}') as date_group,
                SUM(total) as revenue,
                COUNT(*) as order_count
            ")
            ->groupBy('date_group')
            ->orderBy('date_group')
            ->get();
        
        return [
            'labels' => $results->pluck('date_group')->toArray(),
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $results->pluck('revenue')->map(fn($v) => (float) $v)->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                ]
            ]
        ];
    }

    /**
     * Get orders by status for chart.
     */
    public function getOrdersByStatus(Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $query = $this->buildBaseQuery($startDate, $endDate, $filters);
        
        $results = $query->selectRaw('
            status,
            COUNT(*) as count,
            SUM(total) as revenue
        ')
        ->groupBy('status')
        ->get();
        
        $statusLabels = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ];
        
        $colors = [
            'pending' => '#f59e0b',
            'processing' => '#3b82f6',
            'completed' => '#10b981',
            'shipped' => '#06b6d4',
            'delivered' => '#8b5cf6',
            'cancelled' => '#6b7280',
            'refunded' => '#ef4444',
        ];
        
        return [
            'labels' => $results->pluck('status')->map(fn($s) => $statusLabels[$s] ?? $s)->toArray(),
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $results->pluck('count')->toArray(),
                    'backgroundColor' => $results->pluck('status')->map(fn($s) => $colors[$s] ?? '#6b7280')->toArray(),
                ]
            ]
        ];
    }

    /**
     * Get top orders.
     */
    public function getTopOrders(Carbon $startDate, Carbon $endDate, int $limit = 10): Collection
    {
        return Order::with(['customer'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    /**
     * Get payment methods distribution.
     */
    public function getPaymentMethodsDistribution(Carbon $startDate, Carbon $endDate): array
    {
        $results = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->selectRaw('
                payment_method,
                COUNT(*) as order_count,
                SUM(total) as revenue
            ')
            ->groupBy('payment_method')
            ->get();
        
        return [
            'labels' => $results->pluck('payment_method')->map(fn($pm) => ucfirst($pm ?? 'Unknown'))->toArray(),
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $results->pluck('revenue')->map(fn($v) => (float) $v)->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'
                    ],
                ]
            ]
        ];
    }

    /**
     * Build base query with filters.
     */
    protected function buildBaseQuery(Carbon $startDate, Carbon $endDate, array $filters = [])
    {
        $query = Order::query()->whereBetween('created_at', [$startDate, $endDate]);
        
        if (!empty($filters['status'])) {
            $query->whereIn('status', (array) $filters['status']);
        }
        
        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }
        
        return $query;
    }
}
