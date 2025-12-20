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
        // Register package config if needed
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
    }
}
