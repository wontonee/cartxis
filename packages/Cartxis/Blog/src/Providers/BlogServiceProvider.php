<?php

declare(strict_types=1);

namespace Cartxis\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use Cartxis\Blog\Services\BlogService;

class BlogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BlogService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
