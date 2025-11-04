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
     * Register admin menu items
     * Note: Menu items are seeded via CustomerMenuSeeder during database seeding
     */
    protected function registerAdminMenuItems(): void
    {
        // Menu items are managed via database seeding (CustomerMenuSeeder)
        // This method is kept for potential dynamic menu registration in the future
    }
}
