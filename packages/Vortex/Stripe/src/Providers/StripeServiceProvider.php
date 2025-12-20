<?php

namespace Vortex\Stripe\Providers;

use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;
use Vortex\Core\Facades\Menu;
use Vortex\Core\Models\Extension;
use Vortex\Core\Models\PaymentMethod;
use Vortex\Core\Services\PaymentGatewayManager;
use Vortex\Stripe\Services\StripeGateway;
use Illuminate\Support\Facades\Schema;

class StripeServiceProvider extends ServiceProvider
{
    protected const EXTENSION_CODE = 'vortex-stripe';

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
        // Ensure payment method exists even if extension is disabled
        $this->seedPaymentMethod();

        // If extensions table exists and this extension is disabled, do not boot routes/gateway.
        if (!$this->isExtensionActive()) {
            return;
        }

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Register Stripe gateway with the payment gateway manager
        $this->registerGateway();

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../Config/stripe.php' => config_path('stripe.php'),
        ], 'stripe-config');
    }

    /**
     * Determines whether this extension should boot.
     *
     * Backwards-compatible behavior:
     * - If the extensions table isn't available yet, boot as normal.
     * - If the extension row doesn't exist yet, create it as installed+active.
     */
    protected function isExtensionActive(): bool
    {
        try {
            if (!Schema::hasTable('extensions')) {
                return true;
            }

            $extension = Extension::firstOrCreate(
                ['code' => self::EXTENSION_CODE],
                [
                    'name' => 'Stripe Payment Gateway',
                    'description' => 'Accept payments via Stripe - credit cards, digital wallets, and more',
                    'version' => '1.0.0',
                    'author' => 'Vortex Commerce',
                    'author_url' => 'https://vortexcommerce.com',
                    'icon' => 'credit-card',
                    'requires' => ['vortex/core' => '^1.0', 'php' => '^8.2'],
                    'config' => [],
                    'installed' => true,
                    'active' => true,
                    'installed_at' => now(),
                ]
            );

            if (!$extension->installed) {
                $extension->update(['installed' => true, 'installed_at' => now()]);
            }

            return (bool) $extension->active;
        } catch (\Throwable $e) {
            return true;
        }
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
