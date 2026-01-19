<?php

declare(strict_types=1);

namespace Cartxis\Reports\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Cartxis\Shop\Models\Order;

class CustomerReportRepository
{
    /**
     * Get customer statistics
     */
    public function getCustomerStatistics(string $startDate, string $endDate): array
    {
        // Get total customers who made orders in the date range
        $totalCustomers = DB::table('users')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->distinct('users.id')
            ->count('users.id');

        $newCustomers = User::whereBetween('created_at', [$startDate, $endDate])->count();

        // Get repeat customers (users with 2+ orders in date range)
        $repeatCustomers = DB::table(DB::raw('(SELECT users.id FROM users 
            INNER JOIN orders ON users.id = orders.user_id 
            WHERE orders.created_at BETWEEN "' . $startDate . '" AND "' . $endDate . '"
            GROUP BY users.id 
            HAVING COUNT(orders.id) >= 2) as repeat_customers'))
            ->count();

        $totalRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['processing', 'completed'])
            ->sum('total');

        $avgOrderValue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['processing', 'completed'])
            ->avg('total');

        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['processing', 'completed'])
            ->count();

        return [
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'repeat_customers' => $repeatCustomers,
            'total_revenue' => (float) $totalRevenue,
            'avg_order_value' => (float) $avgOrderValue,
            'total_orders' => $totalOrders,
            'avg_lifetime_value' => $totalCustomers > 0 ? (float) ($totalRevenue / $totalCustomers) : 0,
            'repeat_rate' => $totalCustomers > 0 ? round(($repeatCustomers / $totalCustomers) * 100, 2) : 0,
        ];
    }

    /**
     * Get top customers by revenue
     */
    public function getTopCustomers(string $startDate, string $endDate, int $limit = 10): array
    {
        return Order::select([
            'users.id as customer_id',
            'users.name as customer_name',
            'users.email as customer_email',
            DB::raw('COUNT(orders.id) as order_count'),
            DB::raw('SUM(orders.total) as total_spent'),
            DB::raw('AVG(orders.total) as avg_order_value'),
        ])
        ->join('users', 'orders.user_id', '=', 'users.id')
        ->whereBetween('orders.created_at', [$startDate, $endDate])
        ->whereIn('orders.status', ['processing', 'completed'])
        ->groupBy('users.id', 'users.name', 'users.email')
        ->orderByDesc('total_spent')
        ->limit($limit)
        ->get()
        ->toArray();
    }

    /**
     * Get customer acquisition trend
     */
    public function getCustomerAcquisitionTrend(string $startDate, string $endDate): array
    {
        return User::select([
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as new_customers')
        ])
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->toArray();
    }

    /**
     * Get customer retention data
     */
    public function getCustomerRetention(string $startDate, string $endDate): array
    {
        $customers = User::select([
            'users.id',
            'users.name',
            'users.email',
            'users.created_at as registration_date',
            DB::raw('COUNT(orders.id) as order_count'),
            DB::raw('MAX(orders.created_at) as last_order_date'),
            DB::raw('DATEDIFF(MAX(orders.created_at), users.created_at) as customer_age_days')
        ])
        ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
        ->whereBetween('users.created_at', [$startDate, $endDate])
        ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at')
        ->having('order_count', '>=', 2)
        ->get()
        ->toArray();

        return $customers;
    }

    /**
     * Get RFM (Recency, Frequency, Monetary) segmentation
     */
    public function getRFMSegmentation(string $startDate, string $endDate): array
    {
        $customers = User::select([
            'users.id',
            'users.name',
            'users.email',
            DB::raw('DATEDIFF(NOW(), MAX(orders.created_at)) as recency_days'),
            DB::raw('COUNT(orders.id) as frequency'),
            DB::raw('SUM(orders.total) as monetary')
        ])
        ->join('orders', 'users.id', '=', 'orders.user_id')
        ->whereBetween('orders.created_at', [$startDate, $endDate])
        ->whereIn('orders.status', ['processing', 'completed'])
        ->groupBy('users.id', 'users.name', 'users.email')
        ->get()
        ->map(function ($customer) {
            // Calculate RFM scores (1-5 scale)
            $recencyScore = $this->calculateRFMScore($customer['recency_days'], [90, 60, 30, 7], true);
            $frequencyScore = $this->calculateRFMScore($customer['frequency'], [1, 2, 5, 10]);
            $monetaryScore = $this->calculateRFMScore($customer['monetary'], [100, 500, 1000, 5000]);

            $customer['recency_score'] = $recencyScore;
            $customer['frequency_score'] = $frequencyScore;
            $customer['monetary_score'] = $monetaryScore;
            $customer['rfm_score'] = $recencyScore + $frequencyScore + $monetaryScore;
            $customer['segment'] = $this->getRFMSegment($recencyScore, $frequencyScore, $monetaryScore);

            return $customer;
        })
        ->toArray();

        return $customers;
    }

    /**
     * Calculate RFM score based on thresholds
     */
    private function calculateRFMScore($value, array $thresholds, bool $reverse = false): int
    {
        $score = 1;
        foreach ($thresholds as $threshold) {
            if ($reverse ? $value <= $threshold : $value >= $threshold) {
                $score++;
            }
        }
        return $score;
    }

    /**
     * Get RFM segment label
     */
    private function getRFMSegment(int $recency, int $frequency, int $monetary): string
    {
        $totalScore = $recency + $frequency + $monetary;

        if ($recency >= 4 && $frequency >= 4 && $monetary >= 4) {
            return 'Champions';
        } elseif ($recency >= 3 && $frequency >= 3 && $monetary >= 3) {
            return 'Loyal Customers';
        } elseif ($recency >= 4) {
            return 'Recent Customers';
        } elseif ($frequency >= 4) {
            return 'Frequent Shoppers';
        } elseif ($monetary >= 4) {
            return 'Big Spenders';
        } elseif ($recency <= 2 && $frequency >= 3) {
            return 'At Risk';
        } elseif ($recency <= 2 && $frequency <= 2) {
            return 'Lost';
        } else {
            return 'Potential Loyalists';
        }
    }

    /**
     * Get geographic distribution
     */
    public function getGeographicDistribution(string $startDate, string $endDate): array
    {
        // Simplified - return empty array for now due to address table structure uncertainty
        return [];
        
        /* Original implementation - commented out due to address table structure
        return User::select([
            DB::raw('COALESCE(addresses.country, "Unknown") as country'),
            DB::raw('COUNT(DISTINCT users.id) as customer_count'),
            DB::raw('COUNT(orders.id) as order_count'),
            DB::raw('SUM(orders.total) as total_revenue')
        ])
        ->leftJoin('addresses', function ($join) {
            $join->on('users.id', '=', 'addresses.user_id')
                ->where('addresses.type', '=', 'billing')
                ->whereNull('addresses.deleted_at');
        })
        ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
        ->whereBetween('users.created_at', [$startDate, $endDate])
        ->whereIn('orders.status', ['processing', 'completed'])
        ->groupBy('country')
        ->orderByDesc('total_revenue')
        ->get()
        ->toArray();
        */
    }

    /**
     * Get customer lifetime value distribution
     */
    public function getLifetimeValueDistribution(string $startDate, string $endDate): array
    {
        $customers = User::select([
            'users.id',
            DB::raw('SUM(orders.total) as lifetime_value')
        ])
        ->join('orders', 'users.id', '=', 'orders.user_id')
        ->whereBetween('orders.created_at', [$startDate, $endDate])
        ->whereIn('orders.status', ['processing', 'completed'])
        ->groupBy('users.id')
        ->get();

        $distribution = [
            '0-100' => 0,
            '100-500' => 0,
            '500-1000' => 0,
            '1000-5000' => 0,
            '5000+' => 0,
        ];

        foreach ($customers as $customer) {
            $ltv = $customer->lifetime_value;
            if ($ltv < 100) {
                $distribution['0-100']++;
            } elseif ($ltv < 500) {
                $distribution['100-500']++;
            } elseif ($ltv < 1000) {
                $distribution['500-1000']++;
            } elseif ($ltv < 5000) {
                $distribution['1000-5000']++;
            } else {
                $distribution['5000+']++;
            }
        }

        return $distribution;
    }
}
