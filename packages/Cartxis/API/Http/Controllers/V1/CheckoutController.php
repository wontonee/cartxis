<?php

namespace Cartxis\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Cartxis\API\Helpers\ApiResponse;
use Cartxis\Cart\Models\Cart;
use Cartxis\Shop\Models\Order;
use Cartxis\Shop\Models\OrderItem;
use Cartxis\Shop\Models\Address;
use Cartxis\Customer\Models\Customer;
use Cartxis\Customer\Models\CustomerAddress;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Core\Models\Currency;
use Cartxis\Core\Services\PaymentGatewayManager;
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
            'cart' => new \Cartxis\API\Http\Resources\CartResource($cart),
            'addresses' => \Cartxis\API\Http\Resources\AddressResource::collection($addresses),
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
            'address_id' => 'required_without_all:first_name,last_name,address_line_1,city,state,country,postal_code|exists:customer_addresses,id',
            'first_name' => 'required_without:address_id|string|max:255',
            'last_name' => 'required_without:address_id|string|max:255',
            'address_line_1' => 'required_without:address_id|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required_without:address_id|string|max:255',
            'state' => 'required_without:address_id|string|max:255',
            'country' => 'required_without:address_id|string|max:255',
            'postal_code' => 'required_without:address_id|string|max:20',
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
            'address_line_1' => 'required_without_all:same_as_shipping,address_id|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required_without_all:same_as_shipping,address_id|string|max:255',
            'state' => 'required_without_all:same_as_shipping,address_id|string|max:255',
            'country' => 'required_without_all:same_as_shipping,address_id|string|max:255',
            'postal_code' => 'required_without_all:same_as_shipping,address_id|string|max:20',
            'phone' => 'nullable|string|max:20',
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
        $availableCodes = $this->getAvailablePaymentMethodCodes();

        $validator = Validator::make($request->all(), [
            'payment_method_code' => 'required|string|in:' . implode(',', $availableCodes),
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
        if (in_array($request->payment_method_code, ['razorpay', 'stripe', 'paypal', 'phonepe'])) {
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

            // PhonePe — generate OAuth token + SDK order token for Flutter SDK
            // Flutter SDK needs merchant_id + order_token + environment, NOT raw credentials.
            if ($request->payment_method_code === 'phonepe') {
                $mode = strtolower((string) $paymentMethod->getConfigValue('mode', 'production'));
                $env  = $mode === 'test' ? 'UAT' : 'PRODUCTION';
                $defaultCurrency = Currency::getDefault();
                $merchantId = $paymentMethod->getConfigValue($mode === 'test' ? 'test_merchant_id' : 'merchant_id');

                try {
                    // Determine amount from cart (in paisa; minimum 100 = ₹1)
                    $cart = Cart::with('items')->where('user_id', $request->user()->id)->first();
                    $amountInPaisa  = 100; // PhonePe minimum
                    $merchantOrderRef = 'CART_' . $request->user()->id . '_' . time();

                    if ($cart && ! $cart->items->isEmpty()) {
                        $subtotal      = $cart->items->sum(fn ($item) => $item->price * $item->quantity);
                        $shippingCost  = session('checkout.shipping_cost', 0);
                        $amountInPaisa = max(100, (int) round(($subtotal + $shippingCost) * 100));
                    }

                    /** @var \Cartxis\PhonePe\Services\PhonePeGateway $gateway */
                    $gateway = app(PaymentGatewayManager::class)->get('phonepe');

                    // Step 1 — OAuth token
                    $authData = $gateway->generateAccessToken();

                    // Step 2 — SDK order token
                    $orderData = $gateway->createSdkOrderToken(
                        $authData['access_token'],
                        $merchantOrderRef,
                        $amountInPaisa
                    );

                    $response['gateway_config'] = [
                        'merchant_id'       => $merchantId,
                        'order_token'       => $orderData['token'],
                        'phonepe_order_id'  => $orderData['orderId'],
                        'expires_at'        => $orderData['expireAt'] ?? null,
                        'merchant_order_id' => $merchantOrderRef,
                        'environment'       => $env,
                        'mode'              => $mode,
                        'currency'          => $defaultCurrency?->code ?? 'INR',
                    ];
                } catch (\Throwable $e) {
                    Log::error('PhonePe setPaymentMethod: token generation failed', [
                        'error' => $e->getMessage(),
                    ]);
                    return ApiResponse::error(
                        'Could not initialize PhonePe payment: ' . $e->getMessage(),
                        null,
                        502,
                        'PHONEPE_INIT_FAILED'
                    );
                }
            }

            // Add additional Stripe specific config and create payment intent
            if ($request->payment_method_code === 'stripe') {
                // Get default currency
                $defaultCurrency = Currency::getDefault();
                $currencyCode = $defaultCurrency ? $defaultCurrency->code : 'USD';

                $response['gateway_config']['currency'] = $currencyCode;

                // Resolve secret/publishable keys honouring test vs live mode
                $stripeMode      = $paymentMethod->getConfigValue('mode', 'live');
                $stripeSecretKey = $stripeMode === 'test'
                    ? ($paymentMethod->getConfigValue('test_secret_key') ?: $paymentMethod->getConfigValue('secret_key'))
                    : $paymentMethod->getConfigValue('secret_key');
                $stripePubKey    = $stripeMode === 'test'
                    ? ($paymentMethod->getConfigValue('test_publishable_key') ?: $paymentMethod->getConfigValue('publishable_key'))
                    : $paymentMethod->getConfigValue('publishable_key');

                $response['gateway_config']['publishable_key'] = $stripePubKey;

                // Create Stripe payment intent
                try {
                    // Get cart total for payment intent
                    $cart = Cart::with(['items.product'])->where('user_id', $request->user()->id)->first();
                    
                    if ($cart && !$cart->items->isEmpty()) {
                        $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);
                        $shippingCost = session('checkout.shipping_cost', 0);
                        $total = $subtotal + $shippingCost;

                        // Initialize Stripe with secret key
                        Stripe::setApiKey($stripeSecretKey);
                        
                        // Set API version for consistency
                        Stripe::setApiVersion('2023-10-16');

                        // Calculate amount in smallest currency unit (cents/paise)
                        $amount = (int)round($total * 100);
                        
                        // Ensure amount is positive
                        if ($amount <= 0) {
                            return ApiResponse::error('Invalid payment amount', null, 400, 'INVALID_AMOUNT');
                        }

                        // Get user information
                        $user = $request->user();

                        // Get shipping address from session
                        $shippingAddress = session('checkout.shipping_address');

                        // Log payment intent creation attempt
                        \Log::info('Creating Stripe Payment Intent', [
                            'amount' => $amount,
                            'currency' => strtolower($currencyCode),
                            'user_id' => $user->id,
                            'cart_id' => $cart->id,
                        ]);

                        // Create payment intent with automatic_payment_methods for mobile
                        $paymentIntentData = [
                            'amount' => $amount, // Amount in smallest currency unit (cents/paise)
                            'currency' => strtolower($currencyCode), // Must be lowercase
                            'automatic_payment_methods' => [
                                'enabled' => true, // REQUIRED for mobile payment sheet
                            ],
                            'description' => 'Order payment for ' . config('app.name'),
                            'metadata' => [
                                'user_id' => $user->id,
                                'user_email' => $user->email,
                                'cart_id' => $cart->id,
                                'integration' => 'mobile_app',
                            ],
                        ];

                        // Add customer email if available
                        if ($user->email) {
                            $paymentIntentData['receipt_email'] = $user->email;
                        }

                        // Add shipping details (REQUIRED for Indian regulations)
                        if ($shippingAddress) {
                            $paymentIntentData['shipping'] = [
                                'name' => ($shippingAddress['first_name'] ?? '') . ' ' . ($shippingAddress['last_name'] ?? ''),
                                'phone' => $shippingAddress['phone'] ?? null,
                                'address' => [
                                    'line1' => $shippingAddress['address_line_1'] ?? '',
                                    'line2' => $shippingAddress['address_line_2'] ?? null,
                                    'city' => $shippingAddress['city'] ?? '',
                                    'state' => $shippingAddress['state'] ?? '',
                                    'postal_code' => $shippingAddress['postal_code'] ?? '',
                                    'country' => $shippingAddress['country'] ?? 'IN',
                                ],
                            ];
                        } elseif ($user->name) {
                            // Fallback: Use user name if no shipping address
                            $paymentIntentData['shipping'] = [
                                'name' => $user->name,
                                'address' => [
                                    'line1' => 'Address not provided',
                                    'city' => 'Unknown',
                                    'state' => 'Unknown',
                                    'postal_code' => '000000',
                                    'country' => 'IN',
                                ],
                            ];
                        }

                        $paymentIntent = PaymentIntent::create($paymentIntentData);

                        // Log successful creation
                        \Log::info('Stripe Payment Intent Created Successfully', [
                            'payment_intent_id' => $paymentIntent->id,
                            'status' => $paymentIntent->status,
                            'amount' => $paymentIntent->amount,
                            'currency' => $paymentIntent->currency,
                        ]);

                        $response['gateway_config']['client_secret'] = $paymentIntent->client_secret;
                        $response['gateway_config']['payment_intent_id'] = $paymentIntent->id;
                        $response['gateway_config']['amount'] = $amount;
                        $response['gateway_config']['status'] = $paymentIntent->status;
                    }
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    \Log::error('Stripe API Error: ' . $e->getMessage(), [
                        'type' => $e->getError()->type ?? null,
                        'code' => $e->getError()->code ?? null,
                        'param' => $e->getError()->param ?? null,
                        'message' => $e->getError()->message ?? null,
                    ]);
                    return ApiResponse::error('Payment gateway error: ' . $e->getMessage(), null, 500, 'STRIPE_API_ERROR');
                } catch (\Exception $e) {
                    \Log::error('Stripe payment intent creation failed: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);
                    return ApiResponse::error('Failed to initialize payment gateway: ' . $e->getMessage(), null, 500, 'PAYMENT_GATEWAY_ERROR');
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
            'user_id' => $request->user()->id,
            'customer_id' => $customer->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'tax' => $tax,
            'total' => $total,
            'notes' => $request->notes,
            'customer_email' => $customer->email ?? $request->user()->email,
            'customer_phone' => $shippingAddress->phone ?? null,
            'source_channel' => 'mobile_app',
        ]);

        // Create order items from cart items
        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product;
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'product_sku' => $product?->sku ?? '',
                'product_name' => $product?->name ?? $cartItem->product_name ?? 'Product',
                'product_image' => $product?->mainImage?->url ?? null,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->price,
                'total' => $cartItem->price * $cartItem->quantity,
                'tax_amount' => 0,
                'discount_amount' => 0,
            ]);
        }

        // Create polymorphic shipping address for the order
        Address::create([
            'addressable_type' => Order::class,
            'addressable_id' => $order->id,
            'type' => Address::TYPE_SHIPPING,
            'first_name' => $shippingAddress->first_name,
            'last_name' => $shippingAddress->last_name,
            'company' => $shippingAddress->company ?? null,
            'phone' => $shippingAddress->phone,
            'email' => $customer->email ?? $request->user()->email,
            'address_line1' => $shippingAddress->address_line_1,
            'address_line2' => $shippingAddress->address_line_2 ?? null,
            'city' => $shippingAddress->city,
            'state' => $shippingAddress->state,
            'postal_code' => $shippingAddress->postal_code,
            'country' => $shippingAddress->country,
            'is_default' => true,
        ]);

        // Use shipping address as billing address too
        Address::create([
            'addressable_type' => Order::class,
            'addressable_id' => $order->id,
            'type' => Address::TYPE_BILLING,
            'first_name' => $shippingAddress->first_name,
            'last_name' => $shippingAddress->last_name,
            'company' => $shippingAddress->company ?? null,
            'phone' => $shippingAddress->phone,
            'email' => $customer->email ?? $request->user()->email,
            'address_line1' => $shippingAddress->address_line_1,
            'address_line2' => $shippingAddress->address_line_2 ?? null,
            'city' => $shippingAddress->city,
            'state' => $shippingAddress->state,
            'postal_code' => $shippingAddress->postal_code,
            'country' => $shippingAddress->country,
            'is_default' => true,
        ]);

        // Clear cart items after order is created successfully
        $cart->items()->delete();

        $currency = Currency::getDefault();

        $responseData = [
            'order_id'        => $order->id,
            'order_number'    => $order->order_number,
            'status'          => $order->status,
            'payment_method'  => $order->payment_method,
            'subtotal'        => (float) $subtotal,
            'shipping_cost'   => (float) $shippingCost,
            'tax'             => (float) $tax,
            'grand_total'     => (float) $total,
            'currency'        => $currency?->code ?? 'INR',
            'currency_symbol' => $currency?->symbol ?? '₹',
        ];

        // Razorpay: create Razorpay order server-side so Flutter SDK gets the order ID
        if ($order->payment_method === 'razorpay') {
            try {
                $gateway = app(PaymentGatewayManager::class)->get('razorpay');
                if ($gateway && $gateway->isConfigured()) {
                    $razorpayData = $gateway->processPayment($order);
                    if (!empty($razorpayData['success']) && isset($razorpayData['payment_data'])) {
                        $pd = $razorpayData['payment_data'];
                        $responseData['razorpay_order_id'] = $pd['razorpay_order_id'];
                        $responseData['razorpay_key_id']   = $pd['razorpay_key_id'];
                        $responseData['razorpay_amount']   = $pd['amount'];
                        $responseData['razorpay_currency'] = $pd['currency'] ?? 'INR';
                    } else {
                        throw new \Exception($razorpayData['message'] ?? 'Failed to create Razorpay order');
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Razorpay processPayment failed in placeOrder', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
                return ApiResponse::error(
                    'Order placed but Razorpay payment could not be initiated: ' . $e->getMessage(),
                    ['order_id' => $order->id, 'order_number' => $order->order_number],
                    502,
                    'RAZORPAY_INIT_FAILED'
                );
            }
        }

        // PhonePe: initiate transaction server-side so Flutter SDK gets the token
        if ($order->payment_method === 'phonepe') {
            try {
                $gateway = app(PaymentGatewayManager::class)->get('phonepe');
                if ($gateway && $gateway->isConfigured()) {
                    $phonePeData = $gateway->initiateForApi($order);
                    $responseData['phonepe_token']      = $phonePeData['phonepe_token'];
                    $responseData['phonepe_order_id']   = $phonePeData['phonepe_order_id'];
                    $responseData['phonepe_expires_at'] = $phonePeData['expires_at'];
                    $responseData['checksum']           = $phonePeData['checksum'];
                    $responseData['merchant_id']        = $phonePeData['merchant_id'];
                }
            } catch (\Exception $e) {
                \Log::error('PhonePe initiateForApi failed in placeOrder', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
                // Order created — return error so Flutter can show a proper message
                return ApiResponse::error(
                    'Order placed but PhonePe payment could not be initiated: ' . $e->getMessage(),
                    ['order_id' => $order->id, 'order_number' => $order->order_number],
                    502,
                    'PHONEPE_INIT_FAILED'
                );
            }
        }

        // PayPal: create PayPal Order (v2) so Flutter gets approve_url + paypal_order_id
        if ($order->payment_method === 'paypal') {
            try {
                $gateway = app(PaymentGatewayManager::class)->get('paypal');
                if (!$gateway) {
                    throw new \Exception('PayPal gateway not registered. Please contact support.');
                }
                if (!$gateway->isConfigured()) {
                    throw new \Exception('PayPal is not configured. Please add your Client ID and Client Secret in the admin panel under Settings → Payment Methods → PayPal.');
                }
                $paypalData = $gateway->processPayment($order);
                if (!empty($paypalData['success']) && isset($paypalData['payment_data'])) {
                    $pd = $paypalData['payment_data'];
                    $responseData['paypal_order_id'] = $pd['paypal_order_id'];
                    $responseData['approve_url']     = $pd['approve_url'];
                } else {
                    throw new \Exception($paypalData['message'] ?? 'Failed to create PayPal order');
                }
            } catch (\Exception $e) {
                \Log::error('PayPal processPayment failed in placeOrder', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
                return ApiResponse::error(
                    'PayPal payment could not be initiated: ' . $e->getMessage(),
                    ['order_id' => $order->id, 'order_number' => $order->order_number],
                    502,
                    'PAYPAL_INIT_FAILED'
                );
            }
        }

        return ApiResponse::success($responseData, 'Order placed successfully', 201);
    }

    /**
     * Verify PhonePe payment after Flutter SDK completes the transaction.
     *
     * Unified payment verification — works for all gateways.
     * Flutter calls this after any payment SDK returns success.
     *
     * POST /api/v1/checkout/verify-payment
     * Body: { order_id, [razorpay_payment_id, razorpay_order_id, razorpay_signature],
     *                    [payment_intent_id], [transaction_id] }
     */
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id'            => 'required|integer',
            'transaction_id'      => 'nullable|string',
            'razorpay_payment_id' => 'nullable|string',
            'razorpay_order_id'   => 'nullable|string',
            'razorpay_signature'  => 'nullable|string',
            'payment_intent_id'   => 'nullable|string',
            'paypal_order_id'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', $validator->errors(), 422);
        }

        $order = Order::where('id', $request->order_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return ApiResponse::error('Order not found', null, 404);
        }

        // Already marked paid — idempotent success
        if ($order->payment_status === Order::PAYMENT_PAID) {
            return ApiResponse::success([
                'verified'       => true,
                'payment_status' => $order->payment_status,
                'order_status'   => $order->status,
                'order_id'       => $order->id,
                'order_number'   => $order->order_number,
            ], 'Payment already verified');
        }

        $paymentMethod = $order->payment_method;
        $existingData  = is_array($order->payment_data)
            ? $order->payment_data
            : json_decode($order->payment_data ?? '{}', true);

        try {
            // ── Razorpay ──────────────────────────────────────────────────────────
            if ($paymentMethod === 'razorpay') {
                if (! $request->razorpay_payment_id || ! $request->razorpay_order_id || ! $request->razorpay_signature) {
                    return ApiResponse::error(
                        'razorpay_payment_id, razorpay_order_id and razorpay_signature are required for Razorpay verification',
                        null, 422, 'MISSING_RAZORPAY_FIELDS'
                    );
                }

                $gateway = app(PaymentGatewayManager::class)->get('razorpay');
                if (!$gateway) {
                    return ApiResponse::error('Razorpay gateway not available', null, 503);
                }

                $verified = $gateway->verifyPayment($order, [
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_order_id'   => $request->razorpay_order_id,
                    'razorpay_signature'  => $request->razorpay_signature,
                ]);

                if ($verified) {
                    $order->update([
                        'payment_status' => Order::PAYMENT_PAID,
                        'status'         => Order::STATUS_PROCESSING,
                        'payment_data'   => json_encode(array_merge($existingData, [
                            'razorpay_payment_id' => $request->razorpay_payment_id,
                            'razorpay_order_id'   => $request->razorpay_order_id,
                            'razorpay_signature'  => $request->razorpay_signature,
                            'verified_at'         => now()->toISOString(),
                        ])),
                    ]);

                    return ApiResponse::success([
                        'verified'       => true,
                        'payment_status' => $order->fresh()->payment_status,
                        'order_status'   => $order->fresh()->status,
                        'order_id'       => $order->id,
                        'order_number'   => $order->order_number,
                    ], 'Payment verified successfully');
                }

                return ApiResponse::error('Razorpay signature verification failed.', ['verified' => false], 422);
            }

            // ── Stripe ────────────────────────────────────────────────────────────
            if ($paymentMethod === 'stripe') {
                $gateway = app(PaymentGatewayManager::class)->get('stripe');
                if (!$gateway) {
                    return ApiResponse::error('Stripe gateway not available', null, 503);
                }

                $verified = $gateway->verifyPayment($order, [
                    'payment_intent_id' => $request->payment_intent_id,
                ]);

                if ($verified) {
                    $order->update([
                        'payment_status' => Order::PAYMENT_PAID,
                        'status'         => Order::STATUS_PROCESSING,
                        'payment_data'   => json_encode(array_merge($existingData, [
                            'payment_intent_id' => $request->payment_intent_id,
                            'verified_at'       => now()->toISOString(),
                        ])),
                    ]);

                    return ApiResponse::success([
                        'verified'       => true,
                        'payment_status' => $order->fresh()->payment_status,
                        'order_status'   => $order->fresh()->status,
                        'order_id'       => $order->id,
                        'order_number'   => $order->order_number,
                    ], 'Payment verified successfully');
                }

                return ApiResponse::error('Stripe payment verification failed.', ['verified' => false], 422);
            }

            // ── PhonePe ───────────────────────────────────────────────────────────
            if ($paymentMethod === 'phonepe') {
                $gateway = app(PaymentGatewayManager::class)->get('phonepe');
                if (!$gateway) {
                    return ApiResponse::error('PhonePe gateway not available', null, 503);
                }

                $verified = $gateway->verifyPayment($order);

                if ($verified) {
                    $order->update([
                        'payment_status' => Order::PAYMENT_PAID,
                        'status'         => Order::STATUS_PROCESSING,
                        'payment_data'   => json_encode(array_merge($existingData, [
                            'transaction_id' => $request->transaction_id,
                            'verified_at'    => now()->toISOString(),
                            'verified_via'   => 'flutter_sdk',
                        ])),
                    ]);

                    return ApiResponse::success([
                        'verified'       => true,
                        'payment_status' => $order->fresh()->payment_status,
                        'order_status'   => $order->fresh()->status,
                        'order_id'       => $order->id,
                        'order_number'   => $order->order_number,
                    ], 'Payment verified successfully');
                }

                return ApiResponse::error('Payment not completed on PhonePe. Please retry or contact support.', [
                    'verified'       => false,
                    'payment_status' => $order->payment_status,
                ], 402);
            }

            // ── PayPal ────────────────────────────────────────────────────────────
            if ($paymentMethod === 'paypal') {
                $gateway = app(PaymentGatewayManager::class)->get('paypal');
                if (!$gateway) {
                    return ApiResponse::error('PayPal gateway not available', null, 503);
                }

                // Backend captures the PayPal order and checks COMPLETED status.
                $paypalOrderId = $request->paypal_order_id
                    ?? ($order->payment_gateway_data['paypal_order_id'] ?? null);

                $verified = $gateway->verifyPayment($order, [
                    'paypal_order_id' => $paypalOrderId,
                ]);

                if ($verified) {
                    $order->update([
                        'payment_status' => Order::PAYMENT_PAID,
                        'status'         => Order::STATUS_PROCESSING,
                        'payment_data'   => json_encode(array_merge($existingData, [
                            'paypal_order_id' => $paypalOrderId,
                            'verified_at'     => now()->toISOString(),
                        ])),
                    ]);

                    return ApiResponse::success([
                        'verified'       => true,
                        'payment_status' => $order->fresh()->payment_status,
                        'order_status'   => $order->fresh()->status,
                        'order_id'       => $order->id,
                        'order_number'   => $order->order_number,
                    ], 'Payment verified successfully');
                }

                return ApiResponse::error('PayPal payment verification failed.', ['verified' => false], 422);
            }

            // ── COD / Bank Transfer — mark as pending, return success ─────────────
            if (in_array($paymentMethod, ['cod', 'bank_transfer'])) {
                return ApiResponse::success([
                    'verified'       => true,
                    'payment_status' => $order->payment_status,
                    'order_status'   => $order->status,
                    'order_id'       => $order->id,
                    'order_number'   => $order->order_number,
                ], 'Order confirmed. Payment pending on delivery/receipt.');
            }

            return ApiResponse::error("Unsupported payment method: {$paymentMethod}", null, 422);

        } catch (\Exception $e) {
            \Log::error('verifyPayment failed', [
                'order_id'       => $order->id,
                'payment_method' => $paymentMethod,
                'error'          => $e->getMessage(),
            ]);

            return ApiResponse::error('Could not verify payment: ' . $e->getMessage(), null, 500);
        }
    }
}
