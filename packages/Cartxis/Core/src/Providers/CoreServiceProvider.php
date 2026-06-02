<?php

namespace Cartxis\Core\Providers;

use Cartxis\Core\Console\Commands\ExtensionMakeCommand;
use Cartxis\Core\Console\Commands\ExtensionsActivateCommand;
use Cartxis\Core\Console\Commands\ExtensionsDeactivateCommand;
use Cartxis\Core\Console\Commands\ExtensionsInstallCommand;
use Cartxis\Core\Console\Commands\ExtensionsListCommand;
use Cartxis\Core\Console\Commands\ExtensionsSyncCommand;
use Cartxis\Core\Console\Commands\ExtensionsUninstallCommand;
use Cartxis\Core\Console\Commands\InstallCommand;
use Cartxis\Core\Console\Commands\TemplateDiscoverCommand;
use Cartxis\Core\Console\Commands\TemplateExportCommand;
use Cartxis\Core\Console\Commands\TemplateInstallCommand;
use Cartxis\Core\Console\Commands\ThemeActivateCommand;
use Cartxis\Core\Console\Commands\ThemeDiscoverCommand;
use Cartxis\Core\Console\Commands\ThemeImportDataCommand;
use Cartxis\Core\Console\Commands\ThemeListCommand;
use Cartxis\Core\Console\Commands\ThemeDirectoryRegisterCommand;
use Cartxis\Core\Http\Middleware\SetAdminSessionCookie;
use Cartxis\Core\Services\ExtensionService;
use Cartxis\Core\Services\HookService;
use Cartxis\Core\Services\MenuService;
use Cartxis\Core\Services\PaymentGatewayManager;
use Cartxis\Core\Services\RemoteThemeDirectoryClient;
use Cartxis\Core\Services\SettingService;
use Cartxis\Core\Services\TemplateCatalogService;
use Cartxis\Core\Services\TemplateInstallService;
use Cartxis\Core\Services\ThemeAssetBuildService;
use Cartxis\Core\Services\ThemeLifecycleService;
use Cartxis\Core\Services\ThemePathResolver;
use Cartxis\Core\Services\ThemeService;
use Cartxis\Core\Services\ThemeViewResolver;
use Cartxis\UIEditor\Services\LayoutService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register HookService as singleton
        $this->app->singleton('cartxis.hook', function ($app) {
            return new HookService;
        });

        // Register MenuService as singleton
        $this->app->singleton('cartxis.menu', function ($app) {
            return new MenuService;
        });

        // Register SettingService as singleton
        $this->app->singleton('cartxis.setting', function ($app) {
            return new SettingService;
        });

        // Register ExtensionService as singleton
        $this->app->singleton('cartxis.extension', function ($app) {
            return new ExtensionService(
                $app->make('cartxis.hook'),
                $app->make('cartxis.menu')
            );
        });

        $this->app->singleton(ThemePathResolver::class, function () {
            return new ThemePathResolver;
        });

        // Register ThemeService as singleton
        $this->app->singleton('cartxis.theme', function ($app) {
            return new ThemeService($app->make(ThemePathResolver::class));
        });

        $this->app->singleton(RemoteThemeDirectoryClient::class, function () {
            return new RemoteThemeDirectoryClient;
        });

        $this->app->singleton(TemplateCatalogService::class, function ($app) {
            return new TemplateCatalogService(
                $app->make(ThemePathResolver::class),
                $app->make(RemoteThemeDirectoryClient::class),
            );
        });

        $this->app->singleton(ThemeLifecycleService::class, function ($app) {
            return new ThemeLifecycleService(
                $app->make('cartxis.theme'),
                $app->make(ThemeAssetBuildService::class),
                $app->make(ThemeViewResolver::class),
            );
        });

        $this->app->singleton(TemplateInstallService::class, function ($app) {
            return new TemplateInstallService(
                $app->make(TemplateCatalogService::class),
                $app->make('cartxis.theme'),
                $app->make(LayoutService::class),
                $app->make(ThemePathResolver::class),
                $app->make(RemoteThemeDirectoryClient::class),
                $app->make(ThemeAssetBuildService::class),
                $app->make(ThemeLifecycleService::class),
            );
        });

        // Bind ThemeService class to service container
        $this->app->bind(ThemeService::class, function ($app) {
            return $app->make('cartxis.theme');
        });

        // Register ThemeViewResolver as singleton
        $this->app->singleton('cartxis.theme.resolver', function ($app) {
            return new ThemeViewResolver($app->make(ThemePathResolver::class));
        });

        // Bind ThemeViewResolver class to service container
        $this->app->bind(ThemeViewResolver::class, function ($app) {
            return $app->make('cartxis.theme.resolver');
        });

        // Register PaymentGatewayManager as singleton
        $this->app->singleton('cartxis.payment.gateway', function ($app) {
            return new PaymentGatewayManager;
        });

        // Bind PaymentGatewayManager class to service container
        $this->app->bind(PaymentGatewayManager::class, function ($app) {
            return $app->make('cartxis.payment.gateway');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                ExtensionsListCommand::class,
                ExtensionsSyncCommand::class,
                ExtensionMakeCommand::class,
                ExtensionsInstallCommand::class,
                ExtensionsUninstallCommand::class,
                ExtensionsActivateCommand::class,
                ExtensionsDeactivateCommand::class,
                ThemeDiscoverCommand::class,
                ThemeListCommand::class,
                ThemeActivateCommand::class,
                ThemeImportDataCommand::class,
                TemplateDiscoverCommand::class,
                TemplateInstallCommand::class,
                TemplateExportCommand::class,
                ThemeDirectoryRegisterCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Give the admin panel its own session cookie so admin and storefront
        // users can be logged in simultaneously in the same browser.
        $this->app->make(Kernel::class)
            ->prependMiddleware(SetAdminSessionCookie::class);

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../Routes/admin.php');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'core');

        // Publish configuration
        $this->publishes([
            __DIR__.'/../Config/core.php' => config_path('core.php'),
        ], 'core-config');

        // Publish translations
        $this->publishes([
            __DIR__.'/../Resources/lang' => lang_path('vendor/core'),
        ], 'core-translations');

        // Boot themes — discover filesystem, load active theme assets
        $this->bootThemes();

        // Boot active extensions
        $this->bootExtensions();
    }

    /**
     * Discover themes from filesystem and boot the active theme's assets/hooks.
     */
    protected function bootThemes(): void
    {
        try {
            $this->removeStaleViteHotFile();

            /** @var ThemeService $themeService */
            $themeService = $this->app->make('cartxis.theme');

            // Auto-discover storefront templates from templates/storefront/ into the DB
            $themeService->discover();

            // Load asset paths and hooks for the currently active theme
            $activeTheme = $themeService->active();

            if ($activeTheme) {
                config(['theme.active' => $activeTheme->slug]);
                $themeService->loadAssets($activeTheme);
            }
        } catch (\Exception $e) {
            // Silently fail during install/migration when tables don't exist yet
        }
    }

    /**
     * Remove public/hot when the Vite dev server is no longer reachable.
     * A stale hot file makes @vite point at :5173 and breaks all JS/CSS the next day.
     */
    protected function removeStaleViteHotFile(): void
    {
        $hotPath = public_path('hot');

        if (! is_file($hotPath)) {
            return;
        }

        $devServerUrl = trim((string) file_get_contents($hotPath));

        if ($devServerUrl === '') {
            @unlink($hotPath);

            return;
        }

        $parts = parse_url($devServerUrl);
        $host = $parts['host'] ?? '127.0.0.1';
        $scheme = $parts['scheme'] ?? 'http';
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        $path = $parts['path'] ?? '/';
        $probeUrl = "{$scheme}://{$host}:{$port}{$path}";

        $reachable = false;

        if (function_exists('curl_init')) {
            $handle = curl_init($probeUrl);
            curl_setopt_array($handle, [
                CURLOPT_NOBODY => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 2,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
            curl_exec($handle);
            $httpCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);
            $reachable = $httpCode >= 200 && $httpCode < 500;
        }

        if ($reachable) {
            return;
        }

        @unlink($hotPath);
    }

    /**
     * Boot active extensions.
     */
    protected function bootExtensions(): void
    {
        try {
            $extensionService = $this->app->make('cartxis.extension');
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
                        $providerPath = rtrim($discovered['path'], '/\\').'/'.ltrim($providerFile, '/\\');
                        if (file_exists($providerPath)) {
                            require_once $providerPath;
                            if (class_exists($providerClass)) {
                                $this->app->register($providerClass);
                            }
                        }

                        continue;
                    }

                    // Legacy fallback
                    $providerPath = $discovered['path'].'/src/'.str_replace('\\', '/', $providerClass).'.php';
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
