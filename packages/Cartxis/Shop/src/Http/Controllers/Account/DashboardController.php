<?php

namespace Cartxis\Shop\Http\Controllers\Account;

use Illuminate\Http\Request;
use Cartxis\Shop\Http\Controllers\Controller;
use Cartxis\Shop\Models\Order;
use Cartxis\Core\Services\ThemeViewResolver;

class DashboardController extends Controller
{
    protected ThemeViewResolver $themeResolver;

    public function __construct(ThemeViewResolver $themeResolver)
    {
        $this->themeResolver = $themeResolver;
    }

    /**
     * Display the customer account dashboard.
     */
    public function index(Request $request)
    {
        $recentOrders = Order::where('customer_id', auth()->id())
            ->orWhere('customer_email', auth()->user()->email)
            ->with(['items'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $this->themeResolver->inertia('Account/Dashboard', [
            'recentOrders' => $recentOrders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total' => $order->total,
                    'created_at' => $order->created_at->format('M d, Y'),
                    'items_count' => $order->items->count(),
                ];
            }),
        ]);
    }
}
