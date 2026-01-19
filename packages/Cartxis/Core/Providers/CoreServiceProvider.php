<?php

namespace Cartxis\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Cartxis\Core\Console\Commands\ExtensionsActivateCommand;
use Cartxis\Core\Console\Commands\ExtensionsDeactivateCommand;
use Cartxis\Core\Console\Commands\ExtensionsInstallCommand;
use Cartxis\Core\Console\Commands\ExtensionsListCommand;
use Cartxis\Core\Console\Commands\ExtensionsSyncCommand;
use Cartxis\Core\Console\Commands\ExtensionsUninstallCommand;
use Cartxis\Core\Services\HookService;
use Cartxis\Core\Services\MenuService;
use Cartxis\Core\Services\ExtensionService;
use Cartxis\Core\Services\SettingService;
use Cartxis\Core\Services\ThemeViewResolver;
use Cartxis\Core\Services\ThemeService;
use Cartxis\Core\Services\PaymentGatewayManager;

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

        // Register ThemeService as singleton
        $this->app->singleton('vortex.theme', function ($app) {
            return new ThemeService();
        });
        
        // Bind ThemeService class to service container
        $this->app->bind(ThemeService::class, function ($app) {
            return $app->make('vortex.theme');
        });
        
        // Register ThemeViewResolver as singleton
        $this->app->singleton('vortex.theme.resolver', function ($app) {
            return new ThemeViewResolver();
        });
        
        // Bind ThemeViewResolver class to service container
        $this->app->bind(ThemeViewResolver::class, function ($app) {
            return $app->make('vortex.theme.resolver');
        });

        // Register PaymentGatewayManager as singleton
        $this->app->singleton('vortex.payment.gateway', function ($app) {
            return new PaymentGatewayManager();
        });
        
        // Bind PaymentGatewayManager class to service container
        $this->app->bind(PaymentGatewayManager::class, function ($app) {
            return $app->make('vortex.payment.gateway');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                ExtensionsListCommand::class,
                ExtensionsSyncCommand::class,
                ExtensionsInstallCommand::class,
                ExtensionsUninstallCommand::class,
                ExtensionsActivateCommand::class,
                ExtensionsDeactivateCommand::class,
            ]);
        }
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

                    // Prefer autoloaded providers (bundled / composer packages)
                    if (class_exists($providerClass)) {
                        $this->app->register($providerClass);
                        continue;
                    }

                    // Support explicit provider file path
                    $providerFile = $discovered['manifest']['provider_file'] ?? null;
                    if ($providerFile) {
                        $providerPath = rtrim($discovered['path'], '/\\') . '/' . ltrim($providerFile, '/\\');
                        if (file_exists($providerPath)) {
                            require_once $providerPath;
                            if (class_exists($providerClass)) {
                                $this->app->register($providerClass);
                            }
                        }
                        continue;
                    }

                    // Legacy fallback
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
