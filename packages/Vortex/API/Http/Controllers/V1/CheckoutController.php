<?php

namespace Vortex\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vortex\API\Helpers\ApiResponse;
use Vortex\Cart\Models\Cart;
use Vortex\Shop\Models\Order;
use Vortex\Customer\Models\Customer;
use Vortex\Customer\Models\CustomerAddress;
use Vortex\Core\Models\PaymentMethod;
use Vortex\Core\Models\Currency;
use Vortex\Core\Services\PaymentGatewayManager;
use Stripe\Stripe;
use Stripe\PaymentIntent;

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
        // Get active payment methods from database
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($method) {
                $data = [
                    'code' => $method->code,
                    'name' => $method->name,
                    'description' => $method->description,
                    'type' => $method->type,
                    'icon' => $method->getConfigValue('icon', 'payment'),
                ];

                // Add gateway credentials for mobile app if it's an online payment method
                if (in_array($method->code, ['razorpay', 'stripe', 'paypal'])) {
                    $data['gateway_config'] = [
                        'key' => $method->getConfigValue('api_key') ?? $method->getConfigValue('public_key'),
                        'environment' => $method->getConfigValue('mode', 'sandbox'),
                    ];

                    // Add Razorpay specific config
                    if ($method->code === 'razorpay') {
                        $data['gateway_config']['key_id'] = $method->getConfigValue('key_id');
                        $data['gateway_config']['currency'] = $method->getConfigValue('currency', 'INR');
                        $data['gateway_config']['name'] = $method->getConfigValue('merchant_name', config('app.name'));
                        $data['gateway_config']['logo'] = $method->getConfigValue('merchant_logo');
                        $data['gateway_config']['theme_color'] = $method->getConfigValue('theme_color', '#3399cc');
                    }

                    // Add Stripe specific config
                    if ($method->code === 'stripe') {
                        $defaultCurrency = Currency::getDefault();
                        $data['gateway_config']['publishable_key'] = $method->getConfigValue('publishable_key');
                        $data['gateway_config']['currency'] = $defaultCurrency ? $defaultCurrency->code : 'USD';
                    }
                }

                return $data;
            });

        return ApiResponse::success($paymentMethods, 'Payment methods retrieved successfully');
    }

    /**
     * Get available payment method codes for validation.
     */
    protected function getAvailablePaymentMethodCodes(): array
    {
        return PaymentMethod::where('is_active', true)
            ->pluck('code')
            ->toArray();
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

        // Get payment method details from database
        $paymentMethod = PaymentMethod::where('code', $request->payment_method_code)
            ->where('is_active', true)
            ->first();

        if (!$paymentMethod) {
            return ApiResponse::error('Payment method not available', null, 400, 'PAYMENT_METHOD_NOT_AVAILABLE');
        }

        session(['checkout.payment_method' => $request->payment_method_code]);

        // Prepare response with payment method details
        $response = [
            'payment_method' => $request->payment_method_code,
            'name' => $paymentMethod->name,
            'description' => $paymentMethod->description,
            'type' => $paymentMethod->type,
            'instructions' => $paymentMethod->instructions,
        ];

        // Add gateway credentials for mobile app if it's an online payment method
        if (in_array($request->payment_method_code, ['razorpay', 'stripe', 'paypal'])) {
            $response['gateway_config'] = [
                'key' => $paymentMethod->getConfigValue('api_key') ?? $paymentMethod->getConfigValue('public_key'),
                'environment' => $paymentMethod->getConfigValue('mode', 'sandbox'),
            ];

            // Add additional Razorpay specific config
            if ($request->payment_method_code === 'razorpay') {
                $response['gateway_config']['key_id'] = $paymentMethod->getConfigValue('key_id');
                $response['gateway_config']['currency'] = $paymentMethod->getConfigValue('currency', 'INR');
                $response['gateway_config']['name'] = $paymentMethod->getConfigValue('merchant_name', config('app.name'));
                $response['gateway_config']['logo'] = $paymentMethod->getConfigValue('merchant_logo');
                $response['gateway_config']['theme_color'] = $paymentMethod->getConfigValue('theme_color', '#3399cc');
            }

            // Add additional Stripe specific config and create payment intent
            if ($request->payment_method_code === 'stripe') {
                // Get default currency
                $defaultCurrency = Currency::getDefault();
                $currencyCode = $defaultCurrency ? $defaultCurrency->code : 'USD';

                $response['gateway_config']['publishable_key'] = $paymentMethod->getConfigValue('publishable_key');
                $response['gateway_config']['currency'] = $currencyCode;

                // Create Stripe payment intent
                try {
                    // Get cart total for payment intent
                    $cart = Cart::with(['items.product'])->where('user_id', $request->user()->id)->first();
                    
                    if ($cart && !$cart->items->isEmpty()) {
                        $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);
                        $shippingCost = session('checkout.shipping_cost', 0);
                        $total = $subtotal + $shippingCost;

                        // Initialize Stripe with secret key
                        Stripe::setApiKey($paymentMethod->getConfigValue('secret_key'));

                        // Create payment intent
                        $paymentIntent = PaymentIntent::create([
                            'amount' => (int)($total * 100), // Amount in cents
                            'currency' => strtolower($currencyCode),
                            'metadata' => [
                                'user_id' => $request->user()->id,
                                'cart_id' => $cart->id,
                            ],
                        ]);

                        $response['gateway_config']['client_secret'] = $paymentIntent->client_secret;
                        $response['gateway_config']['payment_intent_id'] = $paymentIntent->id;
                    }
                } catch (\Exception $e) {
                    \Log::error('Stripe payment intent creation failed: ' . $e->getMessage());
                    return ApiResponse::error('Failed to initialize payment gateway', null, 500, 'PAYMENT_GATEWAY_ERROR');
                }
            }
        }

        return ApiResponse::success($response, 'Payment method set successfully');
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
        $availablePaymentMethods = $this->getAvailablePaymentMethodCodes();
        
        if (empty($availablePaymentMethods)) {
            return ApiResponse::error('No payment methods available', null, 400, 'NO_PAYMENT_METHODS');
        }

        $validator = Validator::make($request->all(), [
            'shipping_address_id' => 'required|exists:customer_addresses,id',
            'payment_method' => 'required|string|in:' . implode(',', $availablePaymentMethods),
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $cart = Cart::with(['items.product'])->where('user_id', $request->user()->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return ApiResponse::error('Cart is empty', null, 400, 'CART_EMPTY');
        }

        // Get customer for this user
        $customer = Customer::where('user_id', $request->user()->id)->first();
        
        if (!$customer) {
            return ApiResponse::error('Customer profile not found', null, 400, 'CUSTOMER_NOT_FOUND');
        }

        // Verify shipping address belongs to customer
        $shippingAddress = CustomerAddress::where('id', $request->shipping_address_id)
            ->where('customer_id', $customer->id)
            ->first();
            
        if (!$shippingAddress) {
            return ApiResponse::error('Invalid shipping address', null, 400, 'INVALID_ADDRESS');
        }

        // Calculate totals
        $subtotal = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        
        $shippingCost = 10.00; // TODO: Calculate based on address/method
        $tax = $subtotal * 0.1; // TODO: Calculate based on address
        $total = $subtotal + $shippingCost + $tax;

        // TODO: Implement order creation logic
        // This should:
        // 1. Create order
        // 2. Create order items
        // 3. Process payment
        // 4. Clear cart
        // 5. Send confirmation email

        $order = Order::create([
            'customer_id' => $customer->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'shipping_address_id' => $request->shipping_address_id,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'tax' => $tax,
            'total' => $total,
            'notes' => $request->notes,
        ]);

        // Clear cart
        $cart->items()->delete();

        return ApiResponse::success([
            'order_id' => $order->id,
            'order_number' => $order->order_number ?? $order->id,
            'status' => $order->status,
            'total' => $total,
        ], 'Order placed successfully', 201);
    }
}
