<?php

namespace Cartxis\Admin\Providers;

use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/admin.php',
            'admin'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../Config/admin.php' => config_path('admin.php'),
        ], 'admin-config');
    }
}
