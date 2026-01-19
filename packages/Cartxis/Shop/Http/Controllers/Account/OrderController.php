<?php

namespace Cartxis\Shop\Http\Controllers\Account;

use Illuminate\Http\Request;
use Cartxis\Shop\Http\Controllers\Controller;
use Cartxis\Shop\Models\Order;
use Cartxis\Core\Services\ThemeViewResolver;

class OrderController extends Controller
{
    protected ThemeViewResolver $themeResolver;

    public function __construct(ThemeViewResolver $themeResolver)
    {
        $this->themeResolver = $themeResolver;
    }

    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request)
    {
        $orders = Order::where('customer_id', auth()->id())
            ->orWhere('customer_email', auth()->user()->email)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->themeResolver->inertia('Account/Orders/Index', [
            'orders' => $orders->through(function ($order) {
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

    /**
     * Display the specified order.
     */
    public function show(Request $request, $id)
    {
        $order = Order::where('id', $id)
            ->where(function ($query) {
                $query->where('customer_id', auth()->id())
                    ->orWhere('customer_email', auth()->user()->email);
            })
            ->with(['items.product', 'shippingAddress', 'billingAddress'])
            ->firstOrFail();

        return $this->themeResolver->inertia('Account/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'subtotal' => $order->subtotal,
                'tax_total' => $order->tax_total,
                'shipping_total' => $order->shipping_total,
                'discount_total' => $order->discount_total,
                'total' => $order->total,
                'notes' => $order->notes,
                'created_at' => $order->created_at->format('M d, Y H:i'),
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'product_slug' => $item->product->slug ?? null,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total' => $item->total,
                    ];
                }),
                'shipping_address' => $order->shippingAddress ? [
                    'first_name' => $order->shippingAddress->first_name,
                    'last_name' => $order->shippingAddress->last_name,
                    'company' => $order->shippingAddress->company,
                    'address_line1' => $order->shippingAddress->address_line1,
                    'address_line2' => $order->shippingAddress->address_line2,
                    'city' => $order->shippingAddress->city,
                    'state' => $order->shippingAddress->state,
                    'postal_code' => $order->shippingAddress->postal_code,
                    'country' => $order->shippingAddress->country,
                    'phone' => $order->shippingAddress->phone,
                ] : null,
                'billing_address' => $order->billingAddress ? [
                    'first_name' => $order->billingAddress->first_name,
                    'last_name' => $order->billingAddress->last_name,
                    'company' => $order->billingAddress->company,
                    'address_line1' => $order->billingAddress->address_line1,
                    'address_line2' => $order->billingAddress->address_line2,
                    'city' => $order->billingAddress->city,
                    'state' => $order->billingAddress->state,
                    'postal_code' => $order->billingAddress->postal_code,
                    'country' => $order->billingAddress->country,
                    'phone' => $order->billingAddress->phone,
                ] : null,
            ],
        ]);
    }
}
