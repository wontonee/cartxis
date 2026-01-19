<?php

namespace Cartxis\PayPal\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Cartxis\Core\Models\Extension;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Core\Services\PaymentGatewayManager;
use Cartxis\PayPal\Services\PayPalGateway;

class PayPalServiceProvider extends ServiceProvider
{
    /**
     * Extension code - must match extension.json
     */
    protected const EXTENSION_CODE = 'cartxis-paypal';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration if needed
        // $this->mergeConfigFrom(__DIR__ . '/../Config/paypal.php', 'paypal');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Always seed payment method (so admins can configure it)
        $this->seedPaymentMethod();

        // Only load routes and register gateway if extension is active
        if (!$this->isExtensionActive()) {
            return;
        }

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Register the gateway
        $this->registerGateway();
    }

    /**
     * Check if the extension is active.
     */
    protected function isExtensionActive(): bool
    {
        try {
            if (!Schema::hasTable('extensions')) {
                return true; // Allow during installation
            }

            $extension = Extension::firstOrCreate(
                ['code' => self::EXTENSION_CODE],
                [
                    'name' => 'PayPal Payment Gateway',
                    'description' => 'Accept payments worldwide using PayPal.',
                    'version' => '1.0.0',
                    'author' => 'Vortex Team',
                    'author_url' => 'https://vortex.test',
                    'icon' => 'credit-card',
                    'requires' => ['php' => '^8.2', 'vortex/core' => '^1.0'],
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
            return true; // Allow during installation
        }
    }

    /**
     * Register the PayPal payment gateway.
     */
    protected function registerGateway(): void
    {
        $manager = $this->app->make(PaymentGatewayManager::class);
        $manager->register(new PayPalGateway());
    }

    /**
     * Seed the payment method in database.
     */
    protected function seedPaymentMethod(): void
    {
        try {
            if (!Schema::hasTable('payment_methods')) {
                return;
            }

            // Check if payment method already exists
            $exists = PaymentMethod::where('code', 'paypal')->exists();

            if (!$exists) {
                PaymentMethod::create([
                    'code' => 'paypal',
                    'name' => 'PayPal',
                    'type' => 'paypal',
                    'description' => 'Pay securely with PayPal. Accept credit cards, debit cards, and PayPal balance.',
                    'instructions' => 'You will be redirected to PayPal to complete your payment.',
                    'is_active' => false, // Admins must configure and enable
                    'is_default' => false,
                    'sort_order' => 20,
                    'configuration' => [
                        'client_id' => '',
                        'client_secret' => '',
                        'mode' => 'sandbox',
                        'webhook_id' => '',
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            // Silent fail during migration/installation
        }
    }
}
