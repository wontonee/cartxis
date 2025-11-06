<?php

declare(strict_types=1);

namespace Vortex\CMS\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Vortex\Core\Models\MenuItem;

class CMSServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/cms.php',
            'cms'
        );

        // Register services
        $this->app->singleton(\Vortex\CMS\Services\PageService::class);
        $this->app->singleton(\Vortex\CMS\Repositories\PageRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../../config/cms.php' => config_path('cms.php'),
        ], 'cms-config');

        // Register admin menu items
        $this->registerAdminMenuItems();
    }

    /**
     * Register admin menu items in the database.
     */
    protected function registerAdminMenuItems(): void
    {
        if ($this->app->runningInConsole()) {
            return; // Skip during console commands
        }

        try {
            // Check if menu_items table exists
            if (!DB::getSchemaBuilder()->hasTable('menu_items')) {
                return;
            }

            // Check if Content parent menu exists
            $contentMenu = MenuItem::where('key', 'content')->first();

            if (!$contentMenu) {
                // Create Content parent menu
                $contentMenu = MenuItem::create([
                    'parent_id' => null,
                    'key' => 'content',
                    'title' => 'Content',
                    'icon' => 'file-text',
                    'route' => null,
                    'location' => 'admin',
                    'order' => 70,
                    'active' => true,
                ]);
            }

            // Create Storefront Menu item if it doesn't exist
            $storefrontMenuItem = MenuItem::where('key', 'content.storefront-menus')->first();
            
            if (!$storefrontMenuItem) {
                MenuItem::create([
                    'parent_id' => $contentMenu->id,
                    'key' => 'content.storefront-menus',
                    'title' => 'Storefront Menu',
                    'icon' => 'menu',
                    'route' => 'admin.content.storefront-menus.index',
                    'location' => 'admin',
                    'order' => 20,
                    'active' => true,
                ]);
            }

            // Pages menu is now managed by AdminMenuSeeder
            // No need to auto-create it here

        } catch (\Exception $e) {
            // Silently fail if database is not ready
            report($e);
        }
    }
}
