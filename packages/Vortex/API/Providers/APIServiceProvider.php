<?php

namespace Vortex\API\Providers;

use Illuminate\Support\ServiceProvider;

class APIServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Load API routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');

        // Load config
        $this->publishes([
            __DIR__ . '/../Config/api.php' => config_path('vortex-api.php'),
        ], 'vortex-api-config');

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/api.php',
            'vortex-api'
        );
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Register API helper
        require_once __DIR__ . '/../Helpers/ApiResponse.php';
    }
}
