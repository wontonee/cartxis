<?php

namespace Cartxis\Shop\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\Cart\Services\CartTaxCalculator;
use Cartxis\Cart\Services\CartShippingCalculator;
use Cartxis\Shop\Services\CheckoutService;
use Cartxis\Shop\Models\ShippingMethod;
use Cartxis\Shop\Models\Order;
use Cartxis\Product\Models\Product;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Core\Models\EmailTemplate;
use Cartxis\Core\Services\ThemeViewResolver;
use Cartxis\Core\Services\SettingService;
use Cartxis\Core\Services\PaymentGatewayManager;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected CartTaxCalculator $taxCalculator;
    protected CartShippingCalculator $shippingCalculator;
    protected CheckoutService $checkoutService;
    protected ThemeViewResolver $themeResolver;
    protected SettingService $settingService;
    protected PaymentGatewayManager $gatewayManager;

    public function __construct(
        CartTaxCalculator $taxCalculator,
        CartShippingCalculator $shippingCalculator,
        CheckoutService $checkoutService,
        ThemeViewResolver $themeResolver,
        SettingService $settingService,
        PaymentGatewayManager $gatewayManager
    ) {
        $this->taxCalculator = $taxCalculator;
        $this->shippingCalculator = $shippingCalculator;
        $this->checkoutService = $checkoutService;
        $this->themeResolver = $themeResolver;
        $this->settingService = $settingService;
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Display checkout page
     */
    public function index(Request $request): Response
    {
        // Get cart items from session
        $items = $this->getCartItems();

        if (empty($items)) {
            return Inertia::render($this->themeResolver->resolve('Checkout/Index'), [
                'error' => 'Your cart is empty',
                'cartEmpty' => true,
                'cartItems' => [],
                'cartSummary' => [
                    'subtotal' => 0,
                    'taxes' => [
                        'breakdown' => [],
                        'total' => 0,
                    ],
                    'shipping' => [
                        'options' => [],
                        'selected' => null,
                        'cost' => 0,
                    ],
                    'total' => 0,
                ],
                'checkoutConfig' => [
                    'allow_guest_checkout' => (bool) $this->settingService->get('checkout_allow_guest', true),
                    'checkout_require_account' => (bool) $this->settingService->get('checkout_require_account', false),
                    'require_terms_acceptance' => (bool) config('shop.checkout.require_terms_acceptance', true),
                    'enable_newsletter_signup' => (bool) config('shop.checkout.enable_newsletter_signup', true),
                    'enable_order_notes' => (bool) config('shop.checkout.enable_order_notes', true),
                ],
                'userAddresses' => [],
                'paymentMethods' => [],
            ]);
        }

        // Calculate subtotal
        $subtotal = collect($items)->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });

        // Calculate taxes
        $taxResult = $this->taxCalculator->calculate($items, []);

        // Calculate shipping options
        $shippingResult = $this->shippingCalculator->calculate($items, []);

        // Get selected shipping from session or use default
        $selectedShippingId = Session::get('checkout.shipping_method_id');
        $selectedShipping = null;

        if ($selectedShippingId) {
            $selectedShipping = collect($shippingResult['options'])->firstWhere('id', $selectedShippingId);
        }

        if (!$selectedShipping) {
            $selectedShipping = $shippingResult['default'] ?? null;
        }

        $shippingCost = $selectedShipping['cost'] ?? 0;
        $grandTotal = $subtotal + $taxResult['total'] + $shippingCost;

        // Get payment methods
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($method) use ($grandTotal) {
                return [
                    'id' => $method->id,
                    'code' => $method->code,
                    'name' => $method->name,
                    'description' => $method->description,
                    'instructions' => $method->instructions,
                    'is_default' => $method->is_default,
                    'fee' => $method->calculateFee($grandTotal),
                    'is_available' => $method->isAvailableForAmount($grandTotal),
                ];
            })
            ->filter(fn($method) => $method['is_available'])
            ->values()
            ->toArray();

        // Get checkout configuration
        $checkoutConfig = [
            'allow_guest_checkout' => (bool) $this->settingService->get('checkout_allow_guest', true),
            'checkout_require_account' => (bool) $this->settingService->get('checkout_require_account', false),
            'require_terms_acceptance' => (bool) config('shop.checkout.require_terms_acceptance', true),
            'enable_newsletter_signup' => (bool) config('shop.checkout.enable_newsletter_signup', true),
            'enable_order_notes' => (bool) config('shop.checkout.enable_order_notes', true),
        ];

        // Get user addresses if authenticated
        $userAddresses = [];
        // TODO: Add addresses() relationship to User model
        // if (Auth::check()) {
        //     $userAddresses = Auth::user()->addresses()
        //         ->where('type', 'shipping')
        //         ->get()
        //         ->map(function ($address) {
        //             return [
        //                 'id' => $address->id,
        //                 'first_name' => $address->first_name,
        //                 'last_name' => $address->last_name,
        //                 'company' => $address->company,
        //                 'address_line1' => $address->address_line1,
        //                 'address_line2' => $address->address_line2,
        //                 'city' => $address->city,
        //                 'state' => $address->state,
        //                 'postal_code' => $address->postal_code,
        //                 'country' => $address->country,
        //                 'phone' => $address->phone,
        //                 'is_default' => $address->is_default,
        //             ];
        //         })
        //         ->toArray();
        // }

        return Inertia::render($this->themeResolver->resolve('Checkout/Index'), [
            'cartItems' => collect($items)->map(function ($item) {
                $product = Product::find($item['product_id']);
                return [
                    'id' => $item['id'] ?? $item['product_id'],
                    'product_id' => $item['product_id'],
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'image' => $product->mainImage?->url,
                    ],
                    'quantity' => $item['quantity'],
                    'price' => round($item['price'], 2),
                    'total' => round($item['quantity'] * $item['price'], 2),
                ];
            })->toArray(),
            'cartSummary' => [
                'subtotal' => round($subtotal, 2),
                'taxes' => [
                    'breakdown' => $taxResult['breakdown'],
                    'total' => round($taxResult['total'], 2),
                ],
                'shipping' => [
                    'options' => $shippingResult['options'],
                    'selected' => $selectedShipping,
                    'cost' => round($shippingCost, 2),
                ],
                'total' => round($grandTotal, 2),
            ],
            'checkoutConfig' => $checkoutConfig,
            'userAddresses' => $userAddresses,
            'paymentMethods' => $paymentMethods,
            'cartEmpty' => false,
        ]);
    }

    /**
     * Process checkout and create order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'shipping_address' => 'required|array',
            'shipping_address.first_name' => 'required|string',
            'shipping_address.last_name' => 'required|string',
            'shipping_address.address_line1' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.state' => 'required|string',
            'shipping_address.postal_code' => 'required|string',
            'shipping_address.country' => 'required|string',
            'shipping_address.phone' => 'required|string',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'payment_method' => 'required|string',
            'billing_same_as_shipping' => 'boolean',
            'billing_address' => 'required_if:billing_same_as_shipping,false|array',
            'terms_accepted' => 'required|accepted',
            'newsletter_signup' => 'boolean',
            'order_notes' => 'nullable|string',
            // Account creation fields
            'create_account' => 'boolean',
            'password' => 'required_if:create_account,true|nullable|string|min:8|confirmed',
            'password_confirmation' => 'required_with:password|nullable|string',
        ]);

        $requireAccount = (bool) $this->settingService->get('checkout_require_account', false);
        $allowGuest = (bool) $this->settingService->get('checkout_allow_guest', true);

        if ($requireAccount && !$allowGuest && !Auth::check()) {
            $request->validate([
                'create_account' => 'accepted',
                'password' => 'required|string|min:8|confirmed',
            ], [
                'create_account.accepted' => 'Account creation is required to complete checkout.',
            ]);
        }

        // Get cart items from session
        $items = $this->getCartItems();

        if (empty($items)) {
            return back()->with('error', 'Your cart is empty');
        }

        // Calculate totals
        $subtotal = collect($items)->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });

        $taxResult = $this->taxCalculator->calculate($items, []);
        $shippingResult = $this->shippingCalculator->calculate($items, []);

        $selectedShipping = collect($shippingResult['options'])
            ->firstWhere('id', $validated['shipping_method_id']);

        if (!$selectedShipping) {
            return back()->with('error', 'Invalid shipping method');
        }

        $shippingCost = $selectedShipping['cost'];
        $grandTotal = $subtotal + $taxResult['total'] + $shippingCost;

        // Prepare order data
        $orderData = [
            'user_id' => Auth::id(),
            'customer_email' => $validated['email'],
            'customer_phone' => $validated['phone'] ?? null,
            'payment_method' => $validated['payment_method'],
            'shipping_method' => $selectedShipping['name'],
            'shipping_address' => $validated['shipping_address'],
            'billing_address' => ($validated['billing_same_as_shipping'] ?? true)
                ? $validated['shipping_address'] 
                : ($validated['billing_address'] ?? $validated['shipping_address']),
            'same_as_shipping' => $validated['billing_same_as_shipping'] ?? true,
            'notes' => $validated['order_notes'] ?? null,
            'subtotal' => $subtotal,
            'tax' => $taxResult['total'],
            'shipping_cost' => $shippingCost,
            'discount' => 0,
            'total' => $grandTotal,
            // Account creation fields
            'create_account' => $validated['create_account'] ?? false,
            'password' => $validated['password'] ?? null,
            'newsletter_subscribed' => $validated['newsletter_signup'] ?? false,
        ];

        // Create order
        $result = $this->checkoutService->createOrder($orderData);

        if (!$result['success']) {
            return back()->with('error', $result['message'] ?? 'Failed to create order');
        }

        $order = $result['data']['order'];

        // Handle payment via gateway manager
        $paymentMethod = $validated['payment_method'];
        
        Log::info('Checkout: Starting payment processing', [
            'order_id' => $order->id,
            'payment_method' => $paymentMethod,
        ]);
        
        // Check if a payment gateway handles this payment method
        $gateway = $this->gatewayManager->getByPaymentMethod($paymentMethod);
        
        Log::info('Checkout: Gateway lookup result', [
            'payment_method' => $paymentMethod,
            'gateway_found' => $gateway ? 'YES' : 'NO',
            'gateway_code' => $gateway ? $gateway->getCode() : 'N/A',
        ]);
        
        if ($gateway) {
            // Gateway exists - check if it's properly configured
            $isConfigured = $gateway->isConfigured();
            
            Log::info('Checkout: Gateway configuration check', [
                'gateway_code' => $gateway->getCode(),
                'is_configured' => $isConfigured ? 'YES' : 'NO',
            ]);
            
            if (!$isConfigured) {
                Log::warning('Checkout: Gateway not configured', [
                    'gateway_code' => $gateway->getCode(),
                ]);
                return back()->with('error', "Payment gateway {$paymentMethod} is not properly configured. Please contact support or try another payment method.");
            }
            
            try {
                Log::info('Checkout: Calling gateway processPayment', [
                    'gateway_code' => $gateway->getCode(),
                    'order_id' => $order->id,
                ]);
                
                // Process payment (will redirect to gateway or return success)
                $response = $this->gatewayManager->processPayment($order, $validated);
                
                Log::info('Checkout: Gateway returned response', [
                    'gateway_code' => $gateway->getCode(),
                    'response_type' => is_object($response) ? get_class($response) : gettype($response),
                ]);
                
                // Handle different response types from payment gateways
                if ($response instanceof \Illuminate\Http\RedirectResponse) {
                    // External redirect (Stripe Checkout, etc.)
                    // Cart will be cleared when user returns successfully
                    $redirectUrl = $response->getTargetUrl();
                    Log::info('Checkout: Returning redirect URL for external gateway', [
                        'url' => $redirectUrl,
                    ]);

                    Session::put('checkout.last_order_id', $order->id);
                    
                    return back()->with([
                        'redirect_url' => $redirectUrl,
                    ]);
                }
                
                // Handle array response (Razorpay, etc. - payment data for frontend)
                if (is_array($response)) {
                    Log::info('Checkout: Returning payment data for frontend integration', [
                        'has_success' => isset($response['success']),
                        'has_payment_data' => isset($response['payment_data']),
                        'payment_data' => $response,
                    ]);
                    
                    // Don't clear cart yet - will be cleared after payment verification in callback
                    // Store payment data in session for redundancy
                    Session::put('razorpay_payment_data', $response);
                    Session::put('checkout.last_order_id', $order->id);
                    
                    // Return back to checkout with payment_response as flash data
                    // Flash data will be automatically included in Inertia props
                    return back()->with([
                        'payment_response' => $response,
                        'redirect_url' => $response['redirect_url'] ?? null,
                    ]);
                }
                
                // For immediate payment methods (COD, etc.), clear cart now
                Session::forget('cart');
                Session::forget('checkout');
                
                // For any other response type, return as-is
                return $response;
                
            } catch (\Exception $e) {
                Log::error('Payment gateway error', [
                    'order_id' => $order->id,
                    'payment_method' => $paymentMethod,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                return back()->with('error', 'Payment processing failed: ' . $e->getMessage());
            }
        }
        
        // No gateway found - treat as COD or immediate payment method
        // This handles COD and any custom payment methods without gateways
        $order->update([
            'payment_status' => Order::PAYMENT_PAID,
            'status' => Order::STATUS_PROCESSING,
        ]);

        // Send order confirmation email
        try {
            $template = EmailTemplate::findByCode('order_placed');
            
            if ($template) {
                $customerName = $validated['shipping_address']['first_name'] . ' ' . 
                               $validated['shipping_address']['last_name'];
                
                $template->send($order->customer_email, [
                    'customer_name' => $customerName,
                    'order_number' => $order->order_number,
                    'order_date' => $order->created_at->format('F j, Y'),
                    'order_total' => '₹' . number_format($order->total, 2),
                    'store_name' => config('app.name', 'Cartxis'),
                    'store_url' => url('/'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Order confirmation email failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        // Clear cart and checkout session
        Session::forget('cart');
        Session::forget('checkout');

        // Redirect to success page
        return redirect()->route('shop.checkout.success', [
            'order' => $order->id
        ])->with('success', 'Order placed successfully!');
    }

    /**
     * Display order success page
     */
    public function success(Request $request, $order)
    {
        $order = \Cartxis\Shop\Models\Order::with(['items', 'addresses'])
                ->where('id', $order)
                ->where('user_id', auth()->id())
            ->firstOrFail();

        // Verify order belongs to current user or session
        if (Auth::check() && $order->user_id !== Auth::id()) {
            abort(403);
        }

        if (Session::get('checkout.last_order_id') === $order->id) {
            Session::forget('cart');
            Session::forget('checkout');
            Session::forget('checkout.last_order_id');
        }

        return Inertia::render($this->themeResolver->resolve('Checkout/Success'), [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'subtotal' => round($order->subtotal, 2),
                'tax' => round($order->tax, 2),
                'shipping_cost' => round($order->shipping_cost, 2),
                'total' => round($order->total, 2),
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'created_at' => $order->created_at->format('F j, Y g:i A'),
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product_name,
                        'product_image' => $item->product_image,
                        'quantity' => $item->quantity,
                        'price' => round($item->price, 2),
                        'total' => round($item->total, 2),
                    ];
                })->toArray(),
                'shipping_address' => $order->shippingAddress() ? [
                    'first_name' => $order->shippingAddress()->first_name,
                    'last_name' => $order->shippingAddress()->last_name,
                    'address_line1' => $order->shippingAddress()->address_line1,
                    'address_line2' => $order->shippingAddress()->address_line2,
                    'city' => $order->shippingAddress()->city,
                    'state' => $order->shippingAddress()->state,
                    'postal_code' => $order->shippingAddress()->postal_code,
                    'country' => $order->shippingAddress()->country,
                    'phone' => $order->shippingAddress()->phone,
                ] : null,
            ],
        ]);
    }

    /**
     * Update selected shipping method in session
     */
    public function updateShipping(Request $request)
    {
        $validated = $request->validate([
            'shipping_method_id' => 'required|exists:shipping_methods,id',
        ]);

        Session::put('checkout.shipping_method_id', $validated['shipping_method_id']);

        return back();
    }
    
    /**
     * Get cart items from session
     */
    private function getCartItems(): array
    {
        $items = Session::get('cart', []);
        
        // Enrich items with product data if needed
        foreach ($items as &$item) {
            if (!isset($item['tax_class_id']) || !isset($item['weight'])) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $item['tax_class_id'] = $product->tax_class_id;
                    $item['weight'] = $product->weight;
                }
            }
        }
        
        return $items;
    }
}
