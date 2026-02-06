<?php

namespace Cartxis\Core\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\Core\Models\Currency;

class DashboardController extends Controller
{
    /**
     * Get default currency
     */
    private function getDefaultCurrency(): ?Currency
    {
        return Currency::getDefault();
    }

    /**
     * Format currency value
     */
    private function formatCurrency(float $amount): string
    {
        $currency = $this->getDefaultCurrency();
        
        if ($currency) {
            return $currency->format($amount);
        }
        
        // Fallback if no default currency is set
        return '$' . number_format($amount, 2);
    }

    /**
     * Display the admin dashboard.
     */
    public function index(): Response
    {
        $currency = $this->getDefaultCurrency();
        
        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => $this->getDashboardStats(),
            'recentOrders' => $this->getRecentOrders(),
            'topProducts' => $this->getTopProducts(),
            'salesChart' => $this->getSalesChartData(),
            'currencySymbol' => $currency ? $currency->symbol : '$',
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    private function getDashboardStats(): array
    {
        $totalRevenue = $this->getTotalRevenue();
        $previousRevenue = $this->getPreviousRevenue();
        $revenueChange = $previousRevenue > 0 
            ? round((($totalRevenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : 0;

        $totalPaidRevenue = $this->getTotalPaidRevenue();
        $previousPaidRevenue = $this->getPreviousPaidRevenue();
        $paidRevenueChange = $previousPaidRevenue > 0
            ? round((($totalPaidRevenue - $previousPaidRevenue) / $previousPaidRevenue) * 100, 1)
            : 0;

        $totalOrders = $this->getTotalOrders();
        $previousOrders = $this->getPreviousOrders();
        $ordersChange = $previousOrders > 0 
            ? round((($totalOrders - $previousOrders) / $previousOrders) * 100, 1)
            : 0;

        $totalCustomers = $this->getTotalCustomers();
        $previousCustomers = $this->getPreviousCustomers();
        $customersChange = $previousCustomers > 0 
            ? round((($totalCustomers - $previousCustomers) / $previousCustomers) * 100, 1)
            : 0;

        $totalProducts = $this->getTotalProducts();
        $previousProducts = $this->getPreviousProducts();
        $productsChange = $previousProducts > 0 
            ? round((($totalProducts - $previousProducts) / $previousProducts) * 100, 1)
            : 0;

        return [
            [
                'title' => 'Completed Revenue',
                'value' => $this->formatCurrency($totalRevenue),
                'change' => ($revenueChange >= 0 ? '+' : '') . $revenueChange . '%',
                'trend' => $revenueChange >= 0 ? 'up' : 'down',
                'icon' => 'DollarSign',
                'color' => 'blue'
            ],
            [
                'title' => 'Paid Revenue',
                'value' => $this->formatCurrency($totalPaidRevenue),
                'change' => ($paidRevenueChange >= 0 ? '+' : '') . $paidRevenueChange . '%',
                'trend' => $paidRevenueChange >= 0 ? 'up' : 'down',
                'icon' => 'CreditCard',
                'color' => 'green'
            ],
            [
                'title' => 'Orders',
                'value' => number_format($totalOrders),
                'change' => ($ordersChange >= 0 ? '+' : '') . $ordersChange . '%',
                'trend' => $ordersChange >= 0 ? 'up' : 'down',
                'icon' => 'ShoppingCart',
                'color' => 'green'
            ],
            [
                'title' => 'Customers',
                'value' => number_format($totalCustomers),
                'change' => ($customersChange >= 0 ? '+' : '') . $customersChange . '%',
                'trend' => $customersChange >= 0 ? 'up' : 'down',
                'icon' => 'Users',
                'color' => 'purple'
            ],
            [
                'title' => 'Products',
                'value' => number_format($totalProducts),
                'change' => ($productsChange >= 0 ? '+' : '') . $productsChange . '%',
                'trend' => $productsChange >= 0 ? 'up' : 'down',
                'icon' => 'Package',
                'color' => 'orange'
            ]
        ];
    }

    /**
     * Get recent orders with customer details
     */
    private function getRecentOrders(): array
    {
        try {
            $orders = DB::table('orders')
                ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'orders.id',
                    'orders.order_number',
                    'users.name as customer',
                    'orders.customer_email',
                    'orders.total as amount',
                    'orders.status',
                    'orders.created_at'
                )
                ->whereNull('orders.deleted_at')
                ->orderBy('orders.created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    // Get product names for this order
                    $products = DB::table('order_items')
                        ->where('order_id', $order->id)
                        ->pluck('product_name')
                        ->toArray();
                    
                    $createdAt = \Carbon\Carbon::parse($order->created_at);
                    $now = \Carbon\Carbon::now();
                    
                    $minutes = (int) $createdAt->diffInMinutes($now);
                    $hours = (int) $createdAt->diffInHours($now);
                    $days = (int) $createdAt->diffInDays($now);
                    
                    if ($minutes < 60) {
                        $date = $minutes . ' min ago';
                    } elseif ($hours < 24) {
                        $date = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
                    } elseif ($days < 7) {
                        $date = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
                    } else {
                        $date = $createdAt->format('M d, Y');
                    }

                    return [
                        'id' => '#' . $order->order_number,
                        'customer' => $order->customer ?? ($order->customer_email ?? 'Guest'),
                        'product' => !empty($products) ? implode(', ', $products) : 'No items',
                        'amount' => $this->formatCurrency((float) $order->amount),
                        'status' => $order->status,
                        'date' => $date
                    ];
                })
                ->toArray();

            return array_values($orders);
        } catch (\Exception $e) {
            \Log::error('Recent Orders Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return [];
        }
    }

    /**
     * Get top selling products
     */
    private function getTopProducts(): array
    {
        try {
            $products = DB::table('order_items')
                ->select(
                    'name',
                    DB::raw('COUNT(*) as sales'),
                    DB::raw('SUM(price * quantity) as revenue')
                )
                ->groupBy('name')
                ->orderBy('sales', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($product, $index) {
                    // Calculate trend (mock for now, would need historical data)
                    $trend = $index % 3 === 0 ? 'down' : 'up';
                    $change = $trend === 'up' ? rand(5, 20) : -rand(2, 8);
                    
                    return [
                        'name' => $product->name,
                        'sales' => (int) $product->sales,
                        'revenue' => $this->formatCurrency((float) $product->revenue),
                        'trend' => $trend,
                        'change' => ($change >= 0 ? '+' : '') . $change . '%'
                    ];
                })
                ->toArray();

            return array_values($products);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get sales chart data for the last 7 days
     */
    private function getSalesChartData(): array
    {
        try {
            $days = [];
            $sales = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $days[] = $date->format('M d');
                
                $daySales = DB::table('orders')
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->whereIn('status', ['completed', 'processing'])
                    ->sum('total');
                    
                $sales[] = (float) $daySales;
            }

            return [
                'labels' => $days,
                'data' => $sales
            ];
        } catch (\Exception $e) {
            return [
                'labels' => [],
                'data' => []
            ];
        }
    }

    private function getTotalProducts(): int
    {
        try {
            return \Cartxis\Product\Models\Product::count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getPreviousProducts(): int
    {
        try {
            return \Cartxis\Product\Models\Product::where('created_at', '<', now()->subMonth())->count();
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
            try {
                return \App\Models\User::count();
            } catch (\Exception $e) {
                return 0;
            }
        }
    }

    private function getPreviousCustomers(): int
    {
        try {
            return \App\Models\User::whereHas('roles', function ($query) {
                $query->where('name', 'customer');
            })->where('created_at', '<', now()->subMonth())->count();
        } catch (\Exception $e) {
            try {
                return \App\Models\User::where('created_at', '<', now()->subMonth())->count();
            } catch (\Exception $e) {
                return 0;
            }
        }
    }

    private function getTotalOrders(): int
    {
        try {
            return DB::table('orders')->whereIn('status', ['completed', 'processing'])->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getPreviousOrders(): int
    {
        try {
            return DB::table('orders')
                ->whereIn('status', ['completed', 'processing'])
                ->where('created_at', '<', now()->subMonth())
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTotalRevenue(): float
    {
        try {
            return (float) DB::table('orders')
                ->where('status', 'completed')
                ->sum('total');
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    private function getPreviousRevenue(): float
    {
        try {
            return (float) DB::table('orders')
                ->where('status', 'completed')
                ->where('created_at', '<', now()->subMonth())
                ->sum('total');
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    private function getTotalPaidRevenue(): float
    {
        try {
            return (float) DB::table('orders')
                ->where('payment_status', 'paid')
                ->sum('total');
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    private function getPreviousPaidRevenue(): float
    {
        try {
            return (float) DB::table('orders')
                ->where('payment_status', 'paid')
                ->where('created_at', '<', now()->subMonth())
                ->sum('total');
        } catch (\Exception $e) {
            return 0.0;
        }
    }
}
