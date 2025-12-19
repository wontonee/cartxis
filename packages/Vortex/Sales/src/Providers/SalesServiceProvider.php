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
        $this->app->singleton(\Vortex\Sales\Repositories\TransactionRepository::class);
        
        // Register services
        $this->app->singleton(\Vortex\Sales\Services\OrderService::class);
        $this->app->singleton(\Vortex\Sales\Services\InvoiceService::class);
        $this->app->singleton(\Vortex\Sales\Services\TransactionService::class);
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
        // Skip if running in console (migrations)
        if ($this->app->runningInConsole()) {
            return;
        }

        if (class_exists(\Vortex\Core\Models\MenuItem::class)) {
            try {
                // Register Sales parent menu
                $salesMenu = \Vortex\Core\Models\MenuItem::firstOrCreate(
                    ['key' => 'sales', 'location' => 'admin'],
                    [
                        'title' => 'Sales',
                        'icon' => 'chart-line',
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
                        'icon' => 'shopping-bag',
                        'route' => 'admin.sales.orders.index',
                        'order' => 10,
                        'parent_id' => $salesMenu->id,
                        'active' => true,
                        'permission' => 'admin.orders.view',
                    ]
                );
                
                // Register Invoices sub-menu
                \Vortex\Core\Models\MenuItem::firstOrCreate(
                    ['key' => 'sales-invoices', 'location' => 'admin'],
                    [
                        'title' => 'Invoices',
                        'icon' => 'document-text',
                        'route' => 'admin.sales.invoices.index',
                        'order' => 20,
                        'parent_id' => $salesMenu->id,
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
                        'parent_id' => $salesMenu->id,
                        'active' => true,
                        'permission' => null,
                    ]
                );
    
                // Register Credit Memos sub-menu
                \Vortex\Core\Models\MenuItem::firstOrCreate(
                    ['key' => 'sales-credit-memos', 'location' => 'admin'],
                    [
                        'title' => 'Credit Memos',
                        'icon' => 'receipt',
                        'route' => 'admin.sales.credit-memos.index',
                        'order' => 40,
                        'parent_id' => $salesMenu->id,
                        'active' => true,
                        'permission' => null,
                    ]
                );
    
                // Register Transactions sub-menu
                \Vortex\Core\Models\MenuItem::firstOrCreate(
                    ['key' => 'sales-transactions', 'location' => 'admin'],
                    [
                        'title' => 'Transactions',
                        'icon' => 'credit-card',
                        'route' => 'admin.sales.transactions.index',
                        'order' => 50,
                        'parent_id' => $salesMenu->id,
                        'active' => true,
                        'permission' => null,
                    ]
                );
            } catch (\Exception $e) {
                // Silently fail during migration when table doesn't exist
            }
        }
    }
}
