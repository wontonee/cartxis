<?php

namespace Vortex\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vortex\API\Helpers\ApiResponse;
use Vortex\Cart\Models\Cart;
use Vortex\Sales\Models\Order;
use Vortex\Customer\Models\CustomerAddress;

class CheckoutController extends Controller
{
    /**
     * Initialize checkout - get cart and addresses.
     */
    public function init(Request $request)
    {
        $cart = Cart::with(['items.product.images', 'items.product.brand'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return ApiResponse::error('Cart is empty', null, 400, 'CART_EMPTY');
        }

        $addresses = CustomerAddress::where('customer_id', $request->user()->id)->get();

        return ApiResponse::success([
            'cart' => new \Vortex\API\Http\Resources\CartResource($cart),
            'addresses' => \Vortex\API\Http\Resources\AddressResource::collection($addresses),
            'default_shipping_address_id' => $addresses->where('is_default_shipping', true)->first()?->id,
            'default_billing_address_id' => $addresses->where('is_default_billing', true)->first()?->id,
        ], 'Checkout initialized successfully');
    }

    /**
     * Set shipping address.
     */
    public function setShippingAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required_without_all:first_name,last_name,address1,city,state,country,zip_code|exists:customer_addresses,id',
            'first_name' => 'required_without:address_id|string|max:255',
            'last_name' => 'required_without:address_id|string|max:255',
            'address1' => 'required_without:address_id|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'required_without:address_id|string|max:255',
            'state' => 'required_without:address_id|string|max:255',
            'country' => 'required_without:address_id|string|max:255',
            'zip_code' => 'required_without:address_id|string|max:20',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        // Store shipping address in session or cart
        session(['checkout.shipping_address' => $request->all()]);

        return ApiResponse::success([
            'shipping_address' => $request->all(),
        ], 'Shipping address set successfully');
    }

    /**
     * Set billing address.
     */
    public function setBillingAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'same_as_shipping' => 'nullable|boolean',
            'address_id' => 'required_without_all:same_as_shipping,first_name|exists:customer_addresses,id',
            'first_name' => 'required_without_all:same_as_shipping,address_id|string|max:255',
            'last_name' => 'required_without_all:same_as_shipping,address_id|string|max:255',
            'address1' => 'required_without_all:same_as_shipping,address_id|string|max:255',
            'city' => 'required_without_all:same_as_shipping,address_id|string|max:255',
            'state' => 'required_without_all:same_as_shipping,address_id|string|max:255',
            'country' => 'required_without_all:same_as_shipping,address_id|string|max:255',
            'zip_code' => 'required_without_all:same_as_shipping,address_id|string|max:20',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        if ($request->same_as_shipping) {
            session(['checkout.billing_address' => session('checkout.shipping_address')]);
        } else {
            session(['checkout.billing_address' => $request->except('same_as_shipping')]);
        }

        return ApiResponse::success([
            'billing_address' => session('checkout.billing_address'),
        ], 'Billing address set successfully');
    }

    /**
     * Get available shipping methods.
     */
    public function getShippingMethods(Request $request)
    {
        // TODO: Implement shipping method calculation based on cart and address
        $shippingMethods = [
            [
                'id' => 1,
                'code' => 'standard',
                'name' => 'Standard Shipping',
                'description' => 'Delivery in 5-7 business days',
                'price' => 5.00,
                'currency' => 'USD',
            ],
            [
                'id' => 2,
                'code' => 'express',
                'name' => 'Express Shipping',
                'description' => 'Delivery in 2-3 business days',
                'price' => 15.00,
                'currency' => 'USD',
            ],
        ];

        return ApiResponse::success($shippingMethods, 'Shipping methods retrieved successfully');
    }

    /**
     * Set shipping method.
     */
    public function setShippingMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipping_method_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        session(['checkout.shipping_method' => $request->shipping_method_code]);

        return ApiResponse::success([
            'shipping_method' => $request->shipping_method_code,
        ], 'Shipping method set successfully');
    }

    /**
     * Get available payment methods.
     */
    public function getPaymentMethods()
    {
        $paymentMethods = [];

        if (config('vortex-api.payment_gateways.stripe')) {
            $paymentMethods[] = [
                'code' => 'stripe',
                'name' => 'Credit/Debit Card',
                'description' => 'Pay securely with your card via Stripe',
                'icon' => 'credit-card',
            ];
        }

        if (config('vortex-api.payment_gateways.razorpay')) {
            $paymentMethods[] = [
                'code' => 'razorpay',
                'name' => 'Razorpay',
                'description' => 'Pay with UPI, Cards, Wallets & more',
                'icon' => 'razorpay',
            ];
        }

        if (config('vortex-api.payment_gateways.cod')) {
            $paymentMethods[] = [
                'code' => 'cod',
                'name' => 'Cash on Delivery',
                'description' => 'Pay when you receive the order',
                'icon' => 'money',
            ];
        }

        return ApiResponse::success($paymentMethods, 'Payment methods retrieved successfully');
    }

    /**
     * Set payment method.
     */
    public function setPaymentMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method_code' => 'required|string|in:stripe,razorpay,cod',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        session(['checkout.payment_method' => $request->payment_method_code]);

        return ApiResponse::success([
            'payment_method' => $request->payment_method_code,
        ], 'Payment method set successfully');
    }

    /**
     * Get checkout summary.
     */
    public function summary(Request $request)
    {
        $cart = Cart::with(['items.product'])->where('user_id', $request->user()->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return ApiResponse::error('Cart is empty', null, 400, 'CART_EMPTY');
        }

        $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);
        $shippingCost = 5.00; // TODO: Calculate from selected shipping method
        $tax = $subtotal * 0.1; // TODO: Calculate based on address
        $discount = $cart->discount_amount ?? 0;
        $total = $subtotal + $shippingCost + $tax - $discount;

        return ApiResponse::success([
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'currency' => 'USD',
            'items_count' => $cart->items->sum('quantity'),
        ], 'Checkout summary retrieved successfully');
    }

    /**
     * Place order.
     */
    public function placeOrder(Request $request)
    {
        $cart = Cart::with(['items.product'])->where('user_id', $request->user()->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return ApiResponse::error('Cart is empty', null, 400, 'CART_EMPTY');
        }

        if (!session('checkout.shipping_address') || !session('checkout.payment_method')) {
            return ApiResponse::error('Checkout incomplete', null, 400, 'CHECKOUT_INCOMPLETE');
        }

        // TODO: Implement order creation logic
        // This should:
        // 1. Create order
        // 2. Create order items
        // 3. Process payment
        // 4. Clear cart
        // 5. Send confirmation email

        $order = Order::create([
            'customer_id' => $request->user()->id,
            'status' => 'pending',
            'payment_method' => session('checkout.payment_method'),
            // ... other order fields
        ]);

        // Clear cart and session
        $cart->items()->delete();
        session()->forget(['checkout']);

        return ApiResponse::success([
            'order_id' => $order->id,
            'order_number' => $order->increment_id,
            'status' => $order->status,
        ], 'Order placed successfully', 201);
    }
}
