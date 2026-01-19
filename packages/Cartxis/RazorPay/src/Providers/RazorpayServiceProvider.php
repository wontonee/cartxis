<?php

namespace Cartxis\Razorpay\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Cartxis\Core\Models\Extension;
use Cartxis\Core\Models\PaymentMethod;
use Cartxis\Core\Services\PaymentGatewayManager;
use Cartxis\Razorpay\Services\RazorpayGateway;

class RazorpayServiceProvider extends ServiceProvider
{
    protected const EXTENSION_CODE = 'cartxis-razorpay';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/razorpay.php',
            'razorpay'
        );
    }

    /**
     * Bootstrap any application services.
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
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');

        // Register the payment gateway
        $this->registerGateway();
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
                    'name' => 'Razorpay Payment Gateway',
                    'description' => 'Accept payments via Razorpay',
                    'version' => '1.0.0',
                    'author' => 'Cartxis Team',
                    'requires' => ['razorpay/razorpay' => '^2.9'],
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
     * Register the Razorpay payment gateway.
     */
    protected function registerGateway(): void
    {
        $manager = $this->app->make(PaymentGatewayManager::class);
        $manager->register(new RazorpayGateway());
    }

    /**
     * Seed the payment method in database.
     */
    protected function seedPaymentMethod(): void
    {
        try {
            // Check if payment method already exists
            $exists = PaymentMethod::where('code', 'razorpay')->exists();

            if (!$exists) {
                PaymentMethod::create([
                    'code' => 'razorpay',
                    'name' => 'Razorpay',
                    'type' => 'other', // Using 'other' since 'razorpay' is not in enum yet
                    'description' => 'Pay securely with Razorpay - Cards, UPI, Netbanking, Wallets',
                    'is_active' => false, // Disabled by default until configured
                    'configuration' => [
                        'key_id' => '',
                        'key_secret' => '',
                        'currency' => 'INR',
                        'webhook_secret' => '',
                        'auto_capture' => true,
                    ],
                    'sort_order' => 2,
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail during migration when table doesn't exist yet
            // The payment method will be seeded after migrations are complete
        }
    }
}
