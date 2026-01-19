<?php

namespace Cartxis\Reports;

use Illuminate\Support\ServiceProvider;

class ReportsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/Config/reports.php',
            'reports'
        );

        // Register services
        $this->app->singleton(\Cartxis\Reports\Services\SalesReportService::class);
        $this->app->singleton(\Cartxis\Reports\Services\ProductReportService::class);
        $this->app->singleton(\Cartxis\Reports\Services\CustomerReportService::class);
        $this->app->singleton(\Cartxis\Reports\Services\ReportCacheService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/Routes/admin.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/Views', 'reports');

        // Publish config
        $this->publishes([
            __DIR__.'/Config/reports.php' => config_path('reports.php'),
        ], 'reports-config');

        // Publish views
        $this->publishes([
            __DIR__.'/Views' => resource_path('views/vendor/reports'),
        ], 'reports-views');
    }
}
