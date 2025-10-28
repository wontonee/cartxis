# Payment Gateway System - Developer Guide

## Overview

Vortex uses a flexible, contract-based payment gateway system that allows developers to create custom payment integrations without modifying core code. Each payment gateway is a separate extension that implements the `PaymentGatewayInterface`.

## Architecture

### Components

1. **PaymentGatewayInterface** - Contract that all gateways must implement
2. **PaymentGatewayManager** - Central registry for all payment gateways
3. **Gateway Extensions** - Individual packages (e.g., Stripe, PayPal, Razorpay)

### Flow

```
Customer Checkout
    ↓
CheckoutController creates order
    ↓
PaymentGatewayManager finds gateway for payment method
    ↓
Gateway processes payment (redirect or immediate)
    ↓
Gateway callback updates order status
    ↓
Order confirmation email sent
```

## Creating a Payment Gateway Extension

### Step 1: Create Package Structure

```
packages/Vortex/YourGateway/
├── src/
│   ├── Services/
│   │   └── YourGateway.php          # Gateway implementation
│   ├── Http/
│   │   └── Controllers/
│   │       └── YourGatewayController.php
│   ├── Providers/
│   │   └── YourGatewayServiceProvider.php
│   ├── Config/
│   │   └── your-gateway.php
│   └── Routes/
│       └── web.php
└── composer.json
```

### Step 2: Implement PaymentGatewayInterface

```php
<?php

namespace Vortex\YourGateway\Services;

use Vortex\Core\Contracts\PaymentGatewayInterface;
use Vortex\Shop\Models\Order;

class YourGateway implements PaymentGatewayInterface
{
    /**
     * Gateway identifier (must match payment method code).
     */
    public function getCode(): string
    {
        return 'your-gateway';
    }

    /**
     * Display name for admin panel.
     */
    public function getName(): string
    {
        return 'Your Gateway Name';
    }

    /**
     * Check if this gateway handles the payment method.
     */
    public function supports(string $paymentMethod): bool
    {
        return $paymentMethod === 'your-gateway';
    }

    /**
     * Process payment for an order.
     * 
     * Return either:
     * - RedirectResponse to your payment page
     * - Array with success status for immediate payment
     * 
     * @throws \Exception on error
     */
    public function processPayment(Order $order, array $data = [])
    {
        // Option 1: Redirect to external payment page
        return redirect($this->generatePaymentUrl($order));
        
        // Option 2: Process payment immediately (COD, bank transfer, etc.)
        return [
            'success' => true,
            'message' => 'Payment accepted',
        ];
    }

    /**
     * Handle callback from payment gateway.
     */
    public function handleCallback(array $data): array
    {
        // Verify payment with your gateway API
        $verified = $this->verifyPaymentWithGateway($data);
        
        return [
            'success' => $verified,
            'order_id' => $data['order_id'],
            'transaction_id' => $data['transaction_id'],
            'message' => $verified ? 'Payment successful' : 'Payment failed',
        ];
    }

    /**
     * Verify payment status.
     */
    public function verifyPayment(Order $order): bool
    {
        // Query your gateway API to verify payment
        return true;
    }

    /**
     * Process refund.
     */
    public function refund(Order $order, ?float $amount = null, ?string $reason = null): array
    {
        // Call your gateway's refund API
        return [
            'success' => true,
            'transaction_id' => 'refund_123',
            'message' => 'Refund processed',
        ];
    }

    /**
     * Configuration fields for admin panel.
     */
    public function getConfigFields(): array
    {
        return [
            [
                'key' => 'api_key',
                'label' => 'API Key',
                'type' => 'text',
                'required' => true,
            ],
            [
                'key' => 'api_secret',
                'label' => 'API Secret',
                'type' => 'password',
                'required' => true,
            ],
            [
                'key' => 'test_mode',
                'label' => 'Test Mode',
                'type' => 'checkbox',
                'required' => false,
            ],
        ];
    }

    /**
     * Check if gateway is configured.
     */
    public function isConfigured(): bool
    {
        return !empty(config('your-gateway.api_key'))
            && !empty(config('your-gateway.api_secret'));
    }
}
```

### Step 3: Create Service Provider

```php
<?php

namespace Vortex\YourGateway\Providers;

use Illuminate\Support\ServiceProvider;
use Vortex\Core\Services\PaymentGatewayManager;
use Vortex\YourGateway\Services\YourGateway;

class YourGatewayServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        
        // Register gateway
        $this->registerGateway();
        
        // Publish config
        $this->publishes([
            __DIR__ . '/../Config/your-gateway.php' => config_path('your-gateway.php'),
        ], 'your-gateway-config');
    }

    protected function registerGateway(): void
    {
        $manager = $this->app->make(PaymentGatewayManager::class);
        $manager->register(new YourGateway());
    }
}
```

### Step 4: Create Routes

```php
<?php
// src/Routes/web.php

use Illuminate\Support\Facades\Route;
use Vortex\YourGateway\Http\Controllers\YourGatewayController;

Route::middleware(['web'])->group(function () {
    // Success callback
    Route::get('your-gateway/success/{order}', [YourGatewayController::class, 'success'])
        ->name('your-gateway.success');
    
    // Cancel/failure callback
    Route::get('your-gateway/cancel/{order}', [YourGatewayController::class, 'cancel'])
        ->name('your-gateway.cancel');
    
    // Webhook (if needed)
    Route::post('your-gateway/webhook', [YourGatewayController::class, 'webhook'])
        ->name('your-gateway.webhook');
});
```

### Step 5: Create Controller

```php
<?php

namespace Vortex\YourGateway\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Vortex\Shop\Models\Order;
use Vortex\Core\Models\EmailTemplate;
use Vortex\Core\Services\PaymentGatewayManager;

class YourGatewayController extends Controller
{
    protected PaymentGatewayManager $gatewayManager;

    public function __construct(PaymentGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
    }

    public function success(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        $gateway = $this->gatewayManager->get('your-gateway');
        
        // Verify payment
        $result = $gateway->handleCallback($request->all());
        
        if (!$result['success']) {
            return redirect()->route('shop.cart.index')
                ->with('error', 'Payment verification failed');
        }
        
        // Update order
        $order->update([
            'payment_status' => Order::PAYMENT_PAID,
            'status' => Order::STATUS_PROCESSING,
            'payment_data' => json_encode([
                'transaction_id' => $result['transaction_id'],
                'verified_at' => now(),
            ]),
        ]);
        
        // Send confirmation email
        $template = EmailTemplate::findByCode('order_placed');
        if ($template) {
            $template->send($order->customer_email, [
                'customer_name' => $order->shippingAddress()->first_name,
                'order_number' => $order->order_number,
                'order_date' => $order->created_at->format('F j, Y'),
                'order_total' => '₹' . number_format($order->total, 2),
                'store_name' => config('app.name'),
                'store_url' => url('/'),
            ]);
        }
        
        return redirect()->route('shop.checkout.success', ['order' => $order->id])
            ->with('success', 'Payment successful!');
    }

    public function cancel($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $order->update([
            'payment_status' => Order::PAYMENT_FAILED,
        ]);
        
        return redirect()->route('shop.cart.index')
            ->with('error', 'Payment was cancelled');
    }
}
```

### Step 6: Register in composer.json

```json
{
    "name": "vortex/your-gateway",
    "description": "Your Gateway payment integration for Vortex",
    "type": "library",
    "autoload": {
        "psr-4": {
            "Vortex\\YourGateway\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Vortex\\YourGateway\\Providers\\YourGatewayServiceProvider"
            ]
        }
    }
}
```

### Step 7: Add to bootstrap/providers.php

```php
return [
    // ... other providers
    Vortex\YourGateway\Providers\YourGatewayServiceProvider::class,
];
```

## Payment Flow Examples

### Redirect-Based Payment (Stripe, PayPal, Razorpay)

```php
public function processPayment(Order $order, array $data = [])
{
    // Create payment session with your gateway
    $session = $this->createPaymentSession($order);
    
    // Store session data in order
    $order->update([
        'payment_data' => json_encode([
            'session_id' => $session->id,
        ]),
    ]);
    
    // Redirect to gateway
    return redirect($session->url);
}
```

### Immediate Payment (COD, Bank Transfer)

```php
public function processPayment(Order $order, array $data = [])
{
    // Mark as paid immediately
    $order->update([
        'payment_status' => Order::PAYMENT_PAID,
        'status' => Order::STATUS_PROCESSING,
    ]);
    
    // Return success (no redirect needed)
    return [
        'success' => true,
        'redirect' => route('shop.checkout.success', ['order' => $order->id]),
    ];
}
```

## Best Practices

1. **Never store sensitive data** - Keep API keys in config files, not database
2. **Always verify callbacks** - Check signatures/tokens from payment gateway
3. **Log everything** - Use Laravel's Log facade for debugging
4. **Handle failures gracefully** - Don't break checkout on payment errors
5. **Test thoroughly** - Use sandbox/test modes before going live
6. **Follow PSR standards** - Keep code clean and documented

## Testing Your Gateway

```php
// Test registration
$manager = app(PaymentGatewayManager::class);
$gateway = $manager->get('your-gateway');
dd($gateway); // Should show your gateway instance

// Test payment processing
$order = Order::find(1);
$result = $manager->processPayment($order);
dd($result); // Should redirect or return success
```

## Example Gateways

### Stripe
- Location: `packages/Vortex/Stripe`
- Uses Stripe Checkout (hosted payment page)
- Handles callbacks via session verification

### Future Gateways
- PayPal
- Razorpay
- PayU
- Square
- Authorize.net

## Support

For questions or issues:
- GitHub: [your-repo]
- Documentation: [your-docs]
- Community: [your-discord/forum]
