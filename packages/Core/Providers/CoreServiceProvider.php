<?php

namespace Vortex\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Vortex\Core\Services\HookService;
use Vortex\Core\Services\MenuService;
use Vortex\Core\Services\ExtensionService;
use Vortex\Core\Services\SettingService;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register HookService as singleton
        $this->app->singleton('vortex.hook', function ($app) {
            return new HookService();
        });

        // Register MenuService as singleton
        $this->app->singleton('vortex.menu', function ($app) {
            return new MenuService();
        });

        // Register SettingService as singleton
        $this->app->singleton('vortex.setting', function ($app) {
            return new SettingService();
        });

        // Register ExtensionService as singleton
        $this->app->singleton('vortex.extension', function ($app) {
            return new ExtensionService(
                $app->make('vortex.hook'),
                $app->make('vortex.menu')
            );
        });
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

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'core');

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../Config/core.php' => config_path('core.php'),
        ], 'core-config');

        // Publish translations
        $this->publishes([
            __DIR__ . '/../Resources/lang' => lang_path('vendor/core'),
        ], 'core-translations');

        // Boot active extensions
        $this->bootExtensions();
    }

    /**
     * Boot active extensions.
     */
    protected function bootExtensions(): void
    {
        try {
            $extensionService = $this->app->make('vortex.extension');
            $activeExtensions = $extensionService->getActive();

            foreach ($activeExtensions as $extension) {
                // Load extension service provider if exists
                $discovered = $extensionService->discover()->firstWhere('manifest.code', $extension->code);
                
                if ($discovered && isset($discovered['manifest']['provider'])) {
                    $providerClass = $discovered['manifest']['provider'];
                    $providerPath = $discovered['path'] . '/src/' . str_replace('\\', '/', $providerClass) . '.php';

                    if (file_exists($providerPath)) {
                        require_once $providerPath;
                        
                        if (class_exists($providerClass)) {
                            $this->app->register($providerClass);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail during installation when tables don't exist yet
        }
    }
}
