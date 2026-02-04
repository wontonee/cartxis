<?php

namespace Cartxis\PhonePe\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Cartxis\Core\Models\Extension;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Core\Services\PaymentGatewayManager;
use Cartxis\PhonePe\Services\PhonePeGateway;

class PhonePeServiceProvider extends ServiceProvider
{
    /**
     * Extension code - must match extension.json
     */
    protected const EXTENSION_CODE = 'cartxis-phonepe';

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/phonepe.php',
            'phonepe'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations from package
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Ensure payment method exists even if extension is disabled
        $this->seedPaymentMethod();

        // If extensions table exists and this extension is disabled, do not boot routes/gateway.
        if (!$this->isExtensionActive()) {
            return;
        }

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Register PhonePe gateway with the payment gateway manager
        $this->registerGateway();

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../Config/phonepe.php' => config_path('phonepe.php'),
        ], 'phonepe-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../Database/Migrations' => database_path('migrations'),
        ], 'phonepe-migrations');
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
                    'name' => 'PhonePe Payment Gateway',
                    'description' => 'Accept payments via PhonePe - UPI, credit cards, debit cards, net banking, and wallets',
                    'version' => '1.0.0',
                    'author' => 'Cartxis Commerce',
                    'author_url' => 'https://cartxis-commerce.com',
                    'icon' => 'smartphone',
                    'requires' => ['cartxis/core' => '^1.0', 'php' => '^8.2'],
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
     * Register PhonePe gateway with the payment gateway manager.
     */
    protected function registerGateway(): void
    {
        try {
            $manager = $this->app->make(PaymentGatewayManager::class);
            $manager->register(new PhonePeGateway());
        } catch (\Throwable $e) {
            // Silently fail if gateway registration fails (e.g., SDK not installed)
            // The payment method will still appear but won't be functional until SDK is installed
            \Illuminate\Support\Facades\Log::warning('PhonePe gateway registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Seed the PhonePe payment method.
     */
    protected function seedPaymentMethod(): void
    {
        try {
            if (!Schema::hasTable('payment_methods')) {
                return;
            }

            // Check if PhonePe payment method already exists
            $exists = PaymentMethod::where('code', 'phonepe')->exists();

            if ($exists) {
                return;
            }

            // Create PhonePe payment method
            PaymentMethod::create([
                'code' => 'phonepe',
                'name' => 'PhonePe',
                'type' => 'phonepe',
                'description' => 'Pay securely using PhonePe with UPI, credit/debit cards, net banking, and wallets',
                'instructions' => 'You will be redirected to PhonePe to complete your payment securely.',
                'is_active' => false,
                'is_default' => false,
                'sort_order' => 5,
                'configuration' => [
                    'client_id' => config('phonepe.phonepe.client_id', ''),
                    'client_secret' => '',
                    'client_version' => config('phonepe.phonepe.client_version', 1),
                    'callback_username' => config('phonepe.phonepe.callback_username', ''),
                    'callback_password' => '',
                    'payment_methods' => [
                        'upi' => true,
                        'card' => true,
                        'netbanking' => true,
                        'wallet' => true,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            // Silently fail during migration when table doesn't exist yet
            // The payment method will be seeded after migrations are complete
        }
    }
}
