<?php

declare(strict_types=1);

namespace Vortex\System\Providers;

use Illuminate\Support\ServiceProvider;
use Vortex\Core\Models\MenuItem;

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

        // Register menu items
        $this->registerMenuItems();
    }

    /**
     * Register system management menu items.
     */
    protected function registerMenuItems(): void
    {
        if ($this->app->runningInConsole() && !$this->app->runningUnitTests()) {
            return;
        }

        try {
            // Create or update System parent menu
            $systemMenu = MenuItem::updateOrCreate(
                ['key' => 'system'],
                [
                    'title' => 'System',
                    'icon' => 'server',
                    'url' => null,
                    'route' => null,
                    'location' => 'admin',
                    'parent_id' => null,
                    'order' => 100,
                    'active' => true,
                ]
            );

            // Create Menu Management submenu
            MenuItem::updateOrCreate(
                ['key' => 'system.menus'],
                [
                    'title' => 'Menu Management',
                    'icon' => 'menu',
                    'route' => 'admin.system.menus.index',
                    'url' => null,
                    'location' => 'admin',
                    'parent_id' => $systemMenu->id,
                    'order' => 0,
                    'active' => true,
                ]
            );

            // Create Cache Management submenu
            MenuItem::updateOrCreate(
                ['key' => 'system.cache'],
                [
                    'title' => 'Cache Management',
                    'icon' => 'database',
                    'route' => 'admin.system.cache.index',
                    'url' => null,
                    'location' => 'admin',
                    'parent_id' => $systemMenu->id,
                    'order' => 1,
                    'active' => true,
                ]
            );

            // Create Maintenance Mode submenu
            MenuItem::updateOrCreate(
                ['key' => 'system.maintenance'],
                [
                    'title' => 'Maintenance Mode',
                    'icon' => 'wrench',
                    'route' => 'admin.system.maintenance.index',
                    'url' => null,
                    'location' => 'admin',
                    'parent_id' => $systemMenu->id,
                    'order' => 2,
                    'active' => true,
                ]
            );
        } catch (\Exception $e) {
            // Silently fail if database is not ready (during installation)
        }
    }
}
