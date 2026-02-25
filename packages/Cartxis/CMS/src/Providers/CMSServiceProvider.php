<?php

declare(strict_types=1);

namespace Cartxis\CMS\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Cartxis\CMS\Repositories\BlockRepository;
use Cartxis\CMS\Services\BlockService;

class CMSServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/cms.php',
            'cms'
        );

        // Register services
            $this->app->singleton(\Cartxis\CMS\Services\PageService::class);
            $this->app->singleton(\Cartxis\CMS\Repositories\PageRepository::class);
            $this->app->singleton(\Cartxis\CMS\Services\BlockService::class);
            $this->app->singleton(\Cartxis\CMS\Repositories\BlockRepository::class);
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
            __DIR__ . '/../config/cms.php' => config_path('cms.php'),
        ], 'cms-config');

        // Register @block Blade directive
        $this->registerBlockDirective();
    }

    /**
     * Register @block Blade directive for rendering blocks.
     */
    protected function registerBlockDirective(): void
    {
        Blade::directive('block', function ($expression) {
            return "<?php echo app(\Cartxis\CMS\Services\BlockService::class)->renderBlock(app(\Cartxis\CMS\Repositories\BlockRepository::class)->findByIdentifier({$expression})); ?>";
        });
    }
}
