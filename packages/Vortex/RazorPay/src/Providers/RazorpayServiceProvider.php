<?php

namespace Vortex\Razorpay\Providers;

use Illuminate\Support\ServiceProvider;
use Vortex\Core\Models\PaymentMethod;
use Vortex\Core\Services\PaymentGatewayManager;
use Vortex\Razorpay\Services\RazorpayGateway;

class RazorpayServiceProvider extends ServiceProvider
{
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
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');

        // Register the payment gateway
        $this->registerGateway();

        // Seed payment method (only if not exists)
        $this->seedPaymentMethod();
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
    }
}
