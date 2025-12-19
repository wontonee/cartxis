<?php

namespace Acme\ExampleGateway\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Vortex\Core\Models\Extension;
use Vortex\Core\Models\PaymentMethod;
use Vortex\Core\Services\PaymentGatewayManager;
use Acme\ExampleGateway\Services\AcmeExampleGateway;

class AcmeExampleServiceProvider extends ServiceProvider
{
    /**
     * Keep this in sync with extension.json `code`.
     */
    protected const EXTENSION_CODE = 'acme-example-gateway';

    public function register(): void
    {
        // Optionally merge config:
        // $this->mergeConfigFrom(__DIR__ . '/../Config/example.php', 'acme_example_gateway');
    }

    public function boot(): void
    {
        // Seed payment method even if extension is disabled (so admins can configure it).
        $this->seedPaymentMethod();

        if (!$this->isExtensionActive()) {
            return;
        }

        // Load routes (optional)
        // $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Register gateway
        $manager = $this->app->make(PaymentGatewayManager::class);
        $manager->register(new AcmeExampleGateway());

        // Load migrations (optional)
        // $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    protected function isExtensionActive(): bool
    {
        try {
            if (!Schema::hasTable('extensions')) {
                return true;
            }

            $extension = Extension::firstOrCreate(
                ['code' => self::EXTENSION_CODE],
                [
                    'name' => 'Acme Example Gateway',
                    'description' => 'Example payment gateway extension for Vortex.',
                    'version' => '1.0.0',
                    'author' => 'Acme Inc.',
                    'author_url' => 'https://acme.test',
                    'icon' => 'credit-card',
                    'requires' => ['php' => '^8.2', 'vortex/core' => '^1.0'],
                    'config' => [],
                    'installed' => false,
                    'active' => false,
                ]
            );

            return (bool) $extension->active;
        } catch (\Throwable $e) {
            return true;
        }
    }

    protected function seedPaymentMethod(): void
    {
        try {
            if (!Schema::hasTable('payment_methods')) {
                return;
            }

            $exists = PaymentMethod::where('code', 'acme_example')->exists();
            if ($exists) {
                return;
            }

            PaymentMethod::create([
                'code' => 'acme_example',
                'name' => 'Acme Example',
                'type' => 'other',
                'description' => 'Example gateway (template).',
                'instructions' => 'You will be redirected to Acme to complete payment.',
                'is_active' => false,
                'is_default' => false,
                'sort_order' => 99,
                'configuration' => [
                    'api_key' => '',
                    'api_secret' => '',
                    'webhook_secret' => '',
                ],
            ]);
        } catch (\Throwable $e) {
            // ignore during early install/migration
        }
    }
}
