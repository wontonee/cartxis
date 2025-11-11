<?php

namespace Vortex\Shop\Services;

use Vortex\Shop\Contracts\OrderRepositoryInterface;
use Vortex\Shop\Models\Order;
use Vortex\Shop\Models\OrderItem;
use Vortex\Shop\Models\Address;
use Vortex\Product\Models\Product;
use Vortex\Customer\Models\Customer;
use Vortex\Customer\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Exception;


class CheckoutService extends ShopService
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Create a new CheckoutService instance.
     *
     * @param  OrderRepositoryInterface  $orderRepository
     * @return void
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Create an order from cart data.
     *
     * @param  array  $data
     * @return array
     */
    public function createOrder(array $data): array
    {
        try {
            DB::beginTransaction();

            // Get cart items from session
            $cartItems = Session::get('cart', []);
            
            if (empty($cartItems)) {
                throw new Exception('Cart is empty');
            }

            // Use provided totals (already calculated in controller with dynamic tax/shipping)
            // or calculate them (fallback for backwards compatibility)
            if (isset($data['subtotal']) && isset($data['tax']) && isset($data['shipping_cost']) && isset($data['total'])) {
                $totals = [
                    'subtotal' => $data['subtotal'],
                    'tax' => $data['tax'],
                    'shipping_cost' => $data['shipping_cost'],
                    'discount' => $data['discount'] ?? 0,
                    'total' => $data['total'],
                ];
            } else {
                // Fallback: calculate totals (legacy behavior)
                $totals = $this->calculateTotals(collect($cartItems), $data);
            }

            // Generate unique order number
            $orderNumber = Order::generateOrderNumber();

            // Handle customer and user account creation
            $userId = $data['user_id'] ?? null;
            $customerId = null;
            
            // If no user_id (guest checkout)
            if (!$userId && isset($data['customer_email'])) {
                // Check if user wants to create an account
                if (!empty($data['create_account']) && !empty($data['password'])) {
                    // Create user account and customer
                    $result = $this->createUserAndCustomer($data);
                    $userId = $result['user_id'];
                    $customerId = $result['customer_id'];
                } else {
                    // Create guest customer only
                    $guestCustomer = $this->getOrCreateGuestCustomer($data);
                    $customerId = $guestCustomer->id;
                }
            }

            // Create order
            $order = $this->orderRepository->create([
                'user_id' => $userId,
                'customer_id' => $customerId,
                'order_number' => $orderNumber,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_PENDING,
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'shipping_cost' => $totals['shipping_cost'],
                'discount' => $totals['discount'],
                'total' => $totals['total'],
                'payment_method' => $data['payment_method'] ?? null,
                'shipping_method' => $data['shipping_method'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                // Handle both array and object cart items
                if (is_array($cartItem)) {
                    $product = Product::find($cartItem['product_id']);
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem['product_id'],
                        'product_sku' => $product->sku ?? null,
                        'product_name' => $product->name ?? 'Product',
                        'product_image' => $product->mainImage?->url ?? null,
                        'quantity' => $cartItem['quantity'],
                        'price' => $cartItem['price'],
                        'total' => $cartItem['quantity'] * $cartItem['price'],
                        'tax_amount' => 0, // TODO: Calculate tax per item
                        'discount_amount' => 0, // TODO: Calculate discount per item
                        'options' => $cartItem['options'] ?? null,
                    ]);
                } else {
                    // Legacy object-based cart items
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'product_sku' => $cartItem->product->sku ?? null,
                        'product_name' => $cartItem->product->name,
                        'product_image' => $cartItem->product->mainImage?->url ?? null,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->price,
                        'total' => $cartItem->quantity * $cartItem->price,
                        'tax_amount' => 0, // TODO: Calculate tax per item
                        'discount_amount' => 0, // TODO: Calculate discount per item
                        'options' => $cartItem->options ?? null,
                    ]);
                }
            }

            // Create shipping address
            if (isset($data['shipping_address'])) {
                $this->createAddress($order, $data['shipping_address'], Address::TYPE_SHIPPING);
                
                // Save to customer address book if this is their first order
                if ($customerId) {
                    $this->saveToCustomerAddressBook($customerId, $data['shipping_address'], 'shipping');
                }
            }

            // Create billing address
            if (isset($data['billing_address'])) {
                $this->createAddress($order, $data['billing_address'], Address::TYPE_BILLING);
                
                // Save to customer address book if this is their first order
                if ($customerId) {
                    $this->saveToCustomerAddressBook($customerId, $data['billing_address'], 'billing');
                }
            } elseif (isset($data['shipping_address']) && ($data['same_as_shipping'] ?? false)) {
                // Use shipping address as billing if specified
                $this->createAddress($order, $data['shipping_address'], Address::TYPE_BILLING);
            }

            // Clear cart after successful order creation (handled in controller)
            // Session::forget('cart');

            DB::commit();

            return $this->formatResponse([
                'order' => $order->load(['items', 'addresses']),
                'order_number' => $orderNumber,
            ], 'Order created successfully');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
            return $this->handleException($e, 'Failed to create order');
        }
    }

    /**
     * Calculate order totals.
     *
     * @param  \Illuminate\Support\Collection  $cartItems
     * @param  array  $data
     * @return array
     */
    protected function calculateTotals($cartItems, array $data): array
    {
        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        $taxRate = config('shop.checkout.tax_rate', 0); // e.g., 0.08 for 8%
        $tax = $subtotal * $taxRate;

        $shippingCost = $this->calculateShipping($data['shipping_method'] ?? null, $subtotal);

        $discount = $this->calculateDiscount($data['discount_code'] ?? null, $subtotal);

        $total = $subtotal + $tax + $shippingCost - $discount;

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping_cost' => round($shippingCost, 2),
            'discount' => round($discount, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Calculate shipping cost.
     *
     * @param  string|null  $shippingMethod
     * @param  float  $subtotal
     * @return float
     */
    protected function calculateShipping(?string $shippingMethod, float $subtotal): float
    {
        // Free shipping threshold
        $freeShippingThreshold = config('shop.checkout.free_shipping_threshold', 100);
        
        if ($subtotal >= $freeShippingThreshold) {
            return 0;
        }

        // Shipping rates
        $rates = config('shop.checkout.shipping_rates', [
            'standard' => 5.00,
            'express' => 15.00,
            'overnight' => 25.00,
        ]);

        return $rates[$shippingMethod] ?? $rates['standard'];
    }

    /**
     * Calculate discount amount.
     *
     * @param  string|null  $discountCode
     * @param  float  $subtotal
     * @return float
     */
    protected function calculateDiscount(?string $discountCode, float $subtotal): float
    {
        if (!$discountCode) {
            return 0;
        }

        // TODO: Implement discount code validation and calculation
        // This would typically query a discounts/coupons table

        return 0;
    }

    /**
     * Create an address for an order.
     *
     * @param  Order  $order
     * @param  array  $addressData
     * @param  string  $type
     * @return Address
     */
    protected function createAddress(Order $order, array $addressData, string $type): Address
    {
        return Address::create([
            'addressable_type' => Order::class,
            'addressable_id' => $order->id,
            'type' => $type,
            'first_name' => $addressData['first_name'],
            'last_name' => $addressData['last_name'],
            'company' => $addressData['company'] ?? null,
            'address_line1' => $addressData['address_line1'],
            'address_line2' => $addressData['address_line2'] ?? null,
            'city' => $addressData['city'],
            'state' => $addressData['state'],
            'postal_code' => $addressData['postal_code'],
            'country' => $addressData['country'],
            'phone' => $addressData['phone'] ?? null,
            'email' => $addressData['email'] ?? null,
        ]);
    }

    /**
     * Save address to customer's address book.
     * Only saves if customer has no saved addresses yet (first order).
     *
     * @param  int  $customerId
     * @param  array  $addressData
     * @param  string  $type
     * @return void
     */
    protected function saveToCustomerAddressBook(int $customerId, array $addressData, string $type): void
    {
        // Check if customer already has saved addresses
        $hasAddresses = CustomerAddress::where('customer_id', $customerId)->exists();
        
        if ($hasAddresses) {
            // Customer already has saved addresses, don't auto-save
            return;
        }

        // This is customer's first order, save the address for future use
        CustomerAddress::create([
            'customer_id' => $customerId,
            'type' => $type,
            'first_name' => $addressData['first_name'],
            'last_name' => $addressData['last_name'],
            'company' => $addressData['company'] ?? null,
            'address_line_1' => $addressData['address_line1'],
            'address_line_2' => $addressData['address_line2'] ?? null,
            'city' => $addressData['city'],
            'state' => $addressData['state'],
            'postal_code' => $addressData['postal_code'],
            'country' => $addressData['country'],
            'phone' => $addressData['phone'] ?? null,
            'is_default_shipping' => $type === 'shipping',
            'is_default_billing' => $type === 'billing',
        ]);
    }

    /**
     * Validate checkout data.
     *
     * @param  array  $data
     * @return array
     */
    public function validateCheckout(array $data): array
    {
        try {
            $errors = [];

            // Validate cart
            $cartItems = Session::get('cart', []);
            if (empty($cartItems)) {
                $errors[] = 'Cart is empty';
            }

            // Validate stock availability
            foreach ($cartItems as $item) {
                $productId = is_array($item) ? $item['product_id'] : $item->product_id;
                $quantity = is_array($item) ? $item['quantity'] : $item->quantity;
                $product = Product::find($productId);
                
                if (!$product || $product->quantity < $quantity) {
                    $errors[] = "Product '{$product->name}' is out of stock";
                }
            }

            // Validate shipping address
            if (empty($data['shipping_address'])) {
                $errors[] = 'Shipping address is required';
            }

            // Validate payment method
            if (empty($data['payment_method'])) {
                $errors[] = 'Payment method is required';
            }

            if (!empty($errors)) {
                return $this->formatResponse(null, 'Validation failed', false, $errors);
            }

            return $this->formatResponse(null, 'Validation successful');

        } catch (Exception $e) {
            return $this->handleException($e, 'Validation failed');
        }
    }

    /**
     * Get order by order number.
     *
     * @param  string  $orderNumber
     * @return array
     */
    public function getOrderByNumber(string $orderNumber): array
    {
        try {
            $order = $this->orderRepository->findByOrderNumber($orderNumber);

            if (!$order) {
                return $this->formatResponse(null, 'Order not found', false);
            }

            $order->load(['items.product', 'addresses', 'user']);

            return $this->formatResponse($order, 'Order retrieved successfully');

        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to retrieve order');
        }
    }

    /**
     * Get user orders.
     *
     * @param  int  $userId
     * @param  int  $perPage
     * @return array
     */
    public function getUserOrders(int $userId, int $perPage = 10): array
    {
        try {
            $orders = $this->orderRepository->getByUser($userId, $perPage);

            return $this->formatResponse($orders, 'Orders retrieved successfully');

        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to retrieve orders');
        }
    }

    /**
     * Cancel an order.
     *
     * @param  int  $orderId
     * @param  int  $userId
     * @return array
     */
    public function cancelOrder(int $orderId, int $userId): array
    {
        try {
            $order = $this->orderRepository->find($orderId);

            if (!$order) {
                return $this->formatResponse(null, 'Order not found', false);
            }

            if ($order->user_id !== $userId) {
                return $this->formatResponse(null, 'Unauthorized', false);
            }

            if (!$order->canBeCancelled()) {
                return $this->formatResponse(null, 'Order cannot be cancelled', false);
            }

            $this->orderRepository->cancel($orderId);

            return $this->formatResponse(null, 'Order cancelled successfully');

        } catch (Exception $e) {
            return $this->handleException($e, 'Failed to cancel order');
        }
    }

    /**
     * Get or create a guest customer record.
     *
     * @param  array  $data
     * @return Customer
     */
    protected function getOrCreateGuestCustomer(array $data): Customer
    {
        $email = $data['customer_email'];
        $firstName = $data['shipping_address']['first_name'] ?? 'Guest';
        $lastName = $data['shipping_address']['last_name'] ?? 'Customer';
        $phone = $data['customer_phone'] ?? $data['shipping_address']['phone'] ?? null;

        // Try to find existing guest customer with this email
        $customer = Customer::where('email', $email)
            ->where('is_guest', true)
            ->first();

        if ($customer) {
            // Update total orders and total spent
            $customer->increment('total_orders');
            $customer->increment('total_spent', $data['total'] ?? 0);
            return $customer;
        }

        // Create new guest customer
        return Customer::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'is_guest' => true,
            'is_active' => true,
            'is_verified' => false,
            'customer_group_id' => null,
            'newsletter_subscribed' => false,
            'total_orders' => 1,
            'total_spent' => $data['total'] ?? 0,
        ]);
    }

    /**
     * Create a user account and customer record during checkout.
     *
     * @param  array  $data
     * @return array ['user_id' => int, 'customer_id' => int]
     * @throws Exception
     */
    protected function createUserAndCustomer(array $data): array
    {
        $email = $data['customer_email'];
        $firstName = $data['shipping_address']['first_name'] ?? 'Customer';
        $lastName = $data['shipping_address']['last_name'] ?? 'Customer';
        $phone = $data['customer_phone'] ?? $data['shipping_address']['phone'] ?? null;
        $password = $data['password'];
        $newsletterSubscribed = $data['newsletter_subscribed'] ?? false;

        // Check if user already exists
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            throw new Exception('An account with this email already exists. Please login instead.');
        }

        // Check if a non-guest customer exists with this email
        $existingCustomer = Customer::where('email', $email)
            ->where('is_guest', false)
            ->first();
        
        if ($existingCustomer) {
            throw new Exception('A customer account with this email already exists.');
        }

        // Create user account
        $user = User::create([
            'name' => trim($firstName . ' ' . $lastName),
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(), // Auto-verify on checkout
        ]);

        Log::info('User account created during checkout', [
            'user_id' => $user->id,
            'email' => $email,
        ]);

        // Check if guest customer exists and convert to registered customer
        $customer = Customer::where('email', $email)
            ->where('is_guest', true)
            ->first();

        if ($customer) {
            // Convert guest to registered customer
            $customer->update([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'is_guest' => false,
                'is_active' => true,
                'is_verified' => true,
                'newsletter_subscribed' => $newsletterSubscribed,
            ]);

            Log::info('Guest customer converted to registered customer', [
                'customer_id' => $customer->id,
                'user_id' => $user->id,
            ]);
        } else {
            // Create new registered customer
            $customer = Customer::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'is_guest' => false,
                'is_active' => true,
                'is_verified' => true,
                'customer_group_id' => null,
                'newsletter_subscribed' => $newsletterSubscribed,
                'total_orders' => 1,
                'total_spent' => $data['total'] ?? 0,
            ]);

            Log::info('New registered customer created during checkout', [
                'customer_id' => $customer->id,
                'user_id' => $user->id,
            ]);
        }

        return [
            'user_id' => $user->id,
            'customer_id' => $customer->id,
        ];
    }
}
