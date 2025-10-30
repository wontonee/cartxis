<?php

namespace Vortex\Sales\Providers;

use Illuminate\Support\ServiceProvider;

class SalesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register repositories
        $this->app->singleton(\Vortex\Sales\Repositories\OrderRepository::class);
        $this->app->singleton(\Vortex\Sales\Repositories\InvoiceRepository::class);
        
        // Register services
        $this->app->singleton(\Vortex\Sales\Services\OrderService::class);
        $this->app->singleton(\Vortex\Sales\Services\InvoiceService::class);
    }

    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../Routes/admin.php');
        
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sales');
        
        // Register menu
        $this->registerMenu();
    }
    
    protected function registerMenu(): void
    {
        if (class_exists(\Vortex\Core\Models\MenuItem::class)) {
            // Register Sales parent menu
            \Vortex\Core\Models\MenuItem::firstOrCreate(
                ['key' => 'sales', 'location' => 'admin'],
                [
                    'title' => 'Sales',
                    'icon' => 'ki-outline ki-chart-line-up',
                    'route' => null,
                    'order' => 30,
                    'active' => true,
                    'permission' => 'admin.sales.view',
                ]
            );
            
            // Register Orders sub-menu
            \Vortex\Core\Models\MenuItem::firstOrCreate(
                ['key' => 'sales-orders', 'location' => 'admin'],
                [
                    'title' => 'Orders',
                    'icon' => 'ki-outline ki-package',
                    'route' => 'admin.sales.orders.index',
                    'order' => 10,
                    'parent_id' => 3, // Sales parent menu ID
                    'active' => true,
                    'permission' => 'admin.orders.view',
                ]
            );
            
            // Register Invoices sub-menu
            \Vortex\Core\Models\MenuItem::firstOrCreate(
                ['key' => 'sales-invoices', 'location' => 'admin'],
                [
                    'title' => 'Invoices',
                    'icon' => 'ki-outline ki-file-sheet',
                    'route' => 'admin.sales.invoices.index',
                    'order' => 20,
                    'parent_id' => 3, // Sales parent menu ID
                    'active' => true,
                    'permission' => 'admin.invoices.view',
                ]
            );

            // Register Shipments sub-menu
            \Vortex\Core\Models\MenuItem::firstOrCreate(
                ['key' => 'sales-shipments', 'location' => 'admin'],
                [
                    'title' => 'Shipments',
                    'icon' => 'package',
                    'route' => 'admin.sales.shipments.index',
                    'order' => 30,
                    'parent_id' => 3, // Sales parent menu ID
                    'active' => true,
                    'permission' => null,
                ]
            );
        }
    }
}
