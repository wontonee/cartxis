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
            ->with(['items.product.mainImage', 'items.product.images', 'shippingAddress', 'billingAddress'])
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
                'subtotal' => (float) ($order->subtotal ?? 0),
                'taxes' => (float) ($order->tax_total ?? 0),
                'shipping' => (float) ($order->shipping_total ?? 0),
                'discount' => (float) ($order->discount_total ?? 0),
                'total' => (float) ($order->total ?? 0),
                'notes' => $order->notes,
                'created_at' => $order->created_at->format('M d, Y H:i'),
                'items' => $order->items->map(function ($item) {
                    $lineTotal = (float) ($item->total ?? 0);
                    $quantity = max((int) ($item->quantity ?? 1), 1);
                    $unitPrice = $item->price ?? $item->unit_price ?? ($lineTotal / $quantity);

                    return [
                        'id' => $item->id,
                        'name' => $item->product_name,
                        'slug' => $item->product?->slug,
                        'image' => $item->product?->image ?? $item->product_image,
                        'quantity' => $item->quantity,
                        'price' => (float) $unitPrice,
                        'subtotal' => $lineTotal,
                    ];
                }),
                'shipping_address' => $order->shippingAddress ? [
                    'first_name' => $order->shippingAddress->first_name,
                    'last_name' => $order->shippingAddress->last_name,
                    'company' => $order->shippingAddress->company,
                    'address_line_1' => $order->shippingAddress->address_line1,
                    'address_line_2' => $order->shippingAddress->address_line2,
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
                    'address_line_1' => $order->billingAddress->address_line1,
                    'address_line_2' => $order->billingAddress->address_line2,
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
