<?php

declare(strict_types=1);

namespace Vortex\Customer\Providers;

use Illuminate\Support\ServiceProvider;

class CustomerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/customer.php');

        // Register admin menu items
        $this->registerAdminMenuItems();
    }

    /**
     * Register admin menu items.
     */
    protected function registerAdminMenuItems(): void
    {
        if (config('admin.menu_items')) {
            config(['admin.menu_items' => array_merge(config('admin.menu_items', []), [
                [
                    'label' => 'Customers',
                    'icon' => 'Users',
                    'route' => null,
                    'children' => [
                        [
                            'label' => 'All Customers',
                            'route' => 'admin.customers.index',
                        ],
                        [
                            'label' => 'Customer Groups',
                            'route' => 'admin.customer-groups.index',
                        ],
                    ],
                ],
            ])]);
        }
    }
}
