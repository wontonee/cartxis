<?php

namespace Vortex\Shop\Providers;

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
            \Vortex\Shop\Contracts\ProductRepositoryInterface::class,
            \Vortex\Shop\Repositories\ProductRepository::class
        );

        $this->app->singleton(
            \Vortex\Shop\Contracts\CategoryRepositoryInterface::class,
            \Vortex\Shop\Repositories\CategoryRepository::class
        );

        $this->app->singleton(
            \Vortex\Shop\Contracts\OrderRepositoryInterface::class,
            \Vortex\Shop\Repositories\OrderRepository::class
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
            \Vortex\Shop\Services\HomeService::class,
            function ($app) {
                return new \Vortex\Shop\Services\HomeService(
                    $app->make(\Vortex\Shop\Contracts\ProductRepositoryInterface::class),
                    $app->make(\Vortex\Shop\Contracts\CategoryRepositoryInterface::class)
                );
            }
        );

        $this->app->singleton(
            \Vortex\Shop\Services\ProductService::class,
            function ($app) {
                return new \Vortex\Shop\Services\ProductService(
                    $app->make(\Vortex\Shop\Contracts\ProductRepositoryInterface::class)
                );
            }
        );

        $this->app->singleton(
            \Vortex\Shop\Services\CategoryService::class,
            function ($app) {
                return new \Vortex\Shop\Services\CategoryService(
                    $app->make(\Vortex\Shop\Contracts\CategoryRepositoryInterface::class)
                );
            }
        );

        $this->app->singleton(
            \Vortex\Shop\Services\CheckoutService::class,
            function ($app) {
                return new \Vortex\Shop\Services\CheckoutService(
                    $app->make(\Vortex\Shop\Contracts\OrderRepositoryInterface::class),
                    $app->make(\Vortex\Cart\Services\CartService::class)
                );
            }
        );
    }
}
