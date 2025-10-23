<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Vortex\Core\Models\Theme;

class ThemeServiceProvider extends ServiceProvider
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
        // Add theme views path to Inertia
        $this->registerThemeViewPaths();
        
        // Share theme data with all views
        $this->shareThemeData();
    }

    /**
     * Register theme view paths with Inertia.
     */
    protected function registerThemeViewPaths(): void
    {
        // This will be called by Inertia when resolving component paths
        Inertia::setRootView('app');
    }

    /**
     * Share theme data with all Inertia views.
     */
    protected function shareThemeData(): void
    {
        // This is handled in HandleInertiaRequests middleware
        // But we can add theme-specific view composers here if needed
        
        View::composer('*', function ($view) {
            $theme = Theme::active();
            if ($theme) {
                $view->with('activeTheme', [
                    'name' => $theme->name,
                    'slug' => $theme->slug,
                    'assets' => [
                        'css' => $theme->asset('css/theme.css'),
                        'js' => $theme->asset('js/theme.js'),
                    ]
                ]);
            }
        });
    }
}
