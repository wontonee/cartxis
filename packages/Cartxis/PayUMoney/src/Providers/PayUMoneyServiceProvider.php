<?php

namespace Cartxis\PayUMoney\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Cartxis\Core\Models\Extension;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Core\Services\PaymentGatewayManager;
use Cartxis\PayUMoney\Services\PayUMoneyGateway;

class PayUMoneyServiceProvider extends ServiceProvider
{
    /**
     * Extension code - must match extension.json
     */
    protected const EXTENSION_CODE = 'cartxis-payumoney';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration if needed
        // $this->mergeConfigFrom(__DIR__ . '/../Config/payumoney.php', 'payumoney');
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
                    'name' => 'PayUMoney Payment Gateway',
                    'description' => 'Accept payments in India using PayUMoney.',
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
     * Register the PayUMoney payment gateway.
     */
    protected function registerGateway(): void
    {
        $manager = $this->app->make(PaymentGatewayManager::class);
        $manager->register(new PayUMoneyGateway());
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
            $exists = PaymentMethod::where('code', 'payumoney')->exists();

            if (!$exists) {
                PaymentMethod::create([
                    'code' => 'payumoney',
                    'name' => 'PayUMoney',
                    'type' => 'other',
                    'description' => 'Pay securely with PayUMoney. Supports credit/debit cards, net banking, UPI, and wallets.',
                    'instructions' => 'You will be redirected to PayUMoney to complete your payment.',
                    'is_active' => false, // Admins must configure and enable
                    'is_default' => false,
                    'sort_order' => 30,
                    'configuration' => [
                        'merchant_key' => '',
                        'merchant_salt' => '',
                        'mode' => 'test',
                        'auth_header' => '',
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            // Silent fail during migration/installation
        }
    }
}
