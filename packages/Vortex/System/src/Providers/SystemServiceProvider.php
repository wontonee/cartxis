<?php

declare(strict_types=1);

namespace Vortex\System\Providers;

use Illuminate\Support\ServiceProvider;

class SystemServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge migration configurations
        $this->mergeConfigFrom(__DIR__ . '/../Config/woocommerce-migration.php', 'woocommerce-migration');
        $this->mergeConfigFrom(__DIR__ . '/../Config/bagisto-migration.php', 'bagisto-migration');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/system.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'system');
        
        // Register migration commands (always register so they can be called via Artisan::call from web)
        $this->commands([
            // WooCommerce migration commands
            \Vortex\System\Console\Commands\Migration\WooCommerce\MigrateAllCommand::class,
            \Vortex\System\Console\Commands\Migration\WooCommerce\MigrateCategoriesCommand::class,
            \Vortex\System\Console\Commands\Migration\WooCommerce\MigrateProductsCommand::class,
            \Vortex\System\Console\Commands\Migration\WooCommerce\MigrateCustomersCommand::class,
            \Vortex\System\Console\Commands\Migration\WooCommerce\MigrateOrdersCommand::class,
            
            // Bagisto migration commands
            \Vortex\System\Console\Commands\Migration\Bagisto\MigrateAllCommand::class,
            \Vortex\System\Console\Commands\Migration\Bagisto\MigrateCategoriesCommand::class,
            \Vortex\System\Console\Commands\Migration\Bagisto\MigrateProductsCommand::class,
            \Vortex\System\Console\Commands\Migration\Bagisto\MigrateCustomersCommand::class,
            \Vortex\System\Console\Commands\Migration\Bagisto\MigrateOrdersCommand::class,
        ]);
    }
}
