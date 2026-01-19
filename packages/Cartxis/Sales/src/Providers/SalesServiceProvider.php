<?php

namespace Cartxis\Sales\Providers;

use Illuminate\Support\ServiceProvider;

class SalesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register repositories
        $this->app->singleton(\Cartxis\Sales\Repositories\OrderRepository::class);
        $this->app->singleton(\Cartxis\Sales\Repositories\InvoiceRepository::class);
        $this->app->singleton(\Cartxis\Sales\Repositories\TransactionRepository::class);
        
        // Register services
        $this->app->singleton(\Cartxis\Sales\Services\OrderService::class);
        $this->app->singleton(\Cartxis\Sales\Services\InvoiceService::class);
        $this->app->singleton(\Cartxis\Sales\Services\TransactionService::class);
    }

    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../Routes/admin.php');
        
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sales');
    }
}
