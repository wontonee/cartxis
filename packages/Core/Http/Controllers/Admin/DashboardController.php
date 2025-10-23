<?php

namespace Vortex\Core\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): Response
    {
        return Inertia::render('admin/dashboard/Index', [
            'stats' => $this->getDashboardStats(),
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    private function getDashboardStats(): array
    {
        return [
            'total_products' => $this->getTotalProducts(),
            'total_customers' => $this->getTotalCustomers(),
            'total_orders' => $this->getTotalOrders(),
            'total_revenue' => $this->getTotalRevenue(),
        ];
    }

    private function getTotalProducts(): int
    {
        try {
            return \Vortex\Product\Models\Product::count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTotalCustomers(): int
    {
        try {
            return \App\Models\User::whereHas('roles', function ($query) {
                $query->where('name', 'customer');
            })->count();
        } catch (\Exception $e) {
            // Fallback: count all non-admin users
            try {
                return \App\Models\User::count();
            } catch (\Exception $e) {
                return 0;
            }
        }
    }

    private function getTotalOrders(): int
    {
        try {
            return \Illuminate\Support\Facades\DB::table('orders')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTotalRevenue(): float
    {
        try {
            return (float) \Illuminate\Support\Facades\DB::table('orders')
                ->whereIn('status', ['completed', 'processing'])
                ->sum('total');
        } catch (\Exception $e) {
            return 0.0;
        }
    }
}
