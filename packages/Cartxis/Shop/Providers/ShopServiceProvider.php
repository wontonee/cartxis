<?php

namespace Cartxis\Shop\Providers;

use Illuminate\Support\ServiceProvider;

class ShopServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
        $this->registerRepositories();
        $this->registerServices();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'shop');
        
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'shop');
        
        $this->publishes([
            __DIR__ . '/../Resources/assets' => public_path('vendor/shop'),
        ], 'shop-assets');
        
        // Register middleware for frontend data sharing
        $this->registerMiddleware();
    }
    
    /**
     * Register middleware.
     *
     * @return void
     */
    protected function registerMiddleware()
    {
        $router = $this->app['router'];
        
        // Add setup incomplete check to web middleware group
        $router->pushMiddlewareToGroup('web', 'setup.incomplete');
        
        // Add to web middleware group
        $router->pushMiddlewareToGroup('web', \Cartxis\Shop\Http\Middleware\ShareFrontendData::class);
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/shop.php',
            'shop'
        );
    }

    /**
     * Register repositories.
     *
     * @return void
     */
    protected function registerRepositories()
    {
        $this->app->singleton(
            \Cartxis\Shop\Contracts\ProductRepositoryInterface::class,
            \Cartxis\Shop\Repositories\ProductRepository::class
        );

        $this->app->singleton(
            \Cartxis\Shop\Contracts\CategoryRepositoryInterface::class,
            \Cartxis\Shop\Repositories\CategoryRepository::class
        );

        $this->app->singleton(
            \Cartxis\Shop\Contracts\OrderRepositoryInterface::class,
            \Cartxis\Shop\Repositories\OrderRepository::class
        );
    }

    /**
     * Register services.
     *
     * @return void
     */
    protected function registerServices()
    {
        $this->app->singleton(
            \Cartxis\Shop\Services\HomeService::class,
            function ($app) {
                return new \Cartxis\Shop\Services\HomeService(
                    $app->make(\Cartxis\Shop\Contracts\ProductRepositoryInterface::class),
                    $app->make(\Cartxis\Shop\Contracts\CategoryRepositoryInterface::class)
                );
            }
        );

        $this->app->singleton(
            \Cartxis\Shop\Services\ProductService::class,
            function ($app) {
                return new \Cartxis\Shop\Services\ProductService(
                    $app->make(\Cartxis\Shop\Contracts\ProductRepositoryInterface::class)
                );
            }
        );

        $this->app->singleton(
            \Cartxis\Shop\Services\CategoryService::class,
            function ($app) {
                return new \Cartxis\Shop\Services\CategoryService(
                    $app->make(\Cartxis\Shop\Contracts\CategoryRepositoryInterface::class)
                );
            }
        );

        $this->app->singleton(
            \Cartxis\Shop\Services\CheckoutService::class,
            function ($app) {
                return new \Cartxis\Shop\Services\CheckoutService(
                    $app->make(\Cartxis\Shop\Contracts\OrderRepositoryInterface::class)
                );
            }
        );
    }
}
