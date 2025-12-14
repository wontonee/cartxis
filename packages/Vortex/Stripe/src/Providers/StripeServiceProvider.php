<?php

namespace Vortex\Stripe\Providers;

use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;
use Vortex\Core\Facades\Menu;
use Vortex\Core\Models\PaymentMethod;
use Vortex\Core\Services\PaymentGatewayManager;
use Vortex\Stripe\Services\StripeGateway;

class StripeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/stripe.php',
            'stripe'
        );

        // Note: API key is set per-request from database configuration
        // in StripeGateway service, not globally here
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Register Stripe gateway with the payment gateway manager
        $this->registerGateway();

        // Register menu items (TODO: Only register if payment-methods menu exists)
        // $this->registerMenu();

        // Seed payment method if not exists
        $this->seedPaymentMethod();

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../Config/stripe.php' => config_path('stripe.php'),
        ], 'stripe-config');
    }

    /**
     * Register Stripe gateway with the payment gateway manager.
     */
    protected function registerGateway(): void
    {
        $manager = $this->app->make(PaymentGatewayManager::class);
        $manager->register(new StripeGateway());
    }

    /**
     * Register menu items for Stripe configuration.
     */
    protected function registerMenu(): void
    {
        Menu::register([
            'key' => 'stripe-configuration',
            'title' => 'Stripe',
            'icon' => 'credit-card',
            'route' => 'admin.stripe.configure',
            'permission' => 'stripe.manage',
            'location' => 'admin',
            'parent_id' => 'payment-methods',
            'order' => 10,
            'extension_code' => 'vortex-stripe',
        ]);
    }

    /**
     * Seed the Stripe payment method.
     */
    protected function seedPaymentMethod(): void
    {
        try {
            // Check if Stripe payment method already exists
            $exists = PaymentMethod::where('code', 'stripe')->exists();

            if ($exists) {
                return;
            }

            // Create Stripe payment method
            PaymentMethod::create([
                'code' => 'stripe',
                'name' => 'Stripe',
                'type' => 'stripe',
                'description' => 'Pay securely using Stripe with credit card, Apple Pay, Google Pay, and more',
                'instructions' => 'You will be redirected to Stripe to complete your payment securely.',
                'is_active' => false,
                'is_default' => false,
                'sort_order' => 2,
                'configuration' => [
                    'public_key' => config('stripe.stripe.public_key'),
                    'enable_3d_secure' => config('stripe.stripe.enable_3d_secure'),
                    'save_payment_method' => config('stripe.stripe.save_payment_method'),
                    'payment_methods' => [
                        'card' => true,
                        'apple_pay' => true,
                        'google_pay' => true,
                        'ideal' => false,
                        'bancontact' => false,
                        'eps' => false,
                        'giropay' => false,
                        'klarna' => false,
                        'p24' => false,
                        'alipay' => false,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            // Silently fail during migration when table doesn't exist yet
            // The payment method will be seeded after migrations are complete
        }
    }
}
