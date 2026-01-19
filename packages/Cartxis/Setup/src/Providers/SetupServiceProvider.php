<?php

declare(strict_types=1);

namespace Cartxis\Setup\Providers;

use Illuminate\Support\ServiceProvider;
use Cartxis\Setup\Http\Middleware\RedirectIfSetupComplete;
use Cartxis\Setup\Http\Middleware\RedirectIfSetupIncomplete;
use Cartxis\Setup\Services\DemoDataService;

class SetupServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register DemoDataService as singleton
        $this->app->singleton(DemoDataService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/setup.php');

        // Register middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('setup.complete', RedirectIfSetupComplete::class);
        $router->aliasMiddleware('setup.incomplete', RedirectIfSetupIncomplete::class);
    }
}
