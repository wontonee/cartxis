<?php

namespace Cartxis\Core\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Cartxis\Core\Models\Theme;

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
     *
     * NOTE: Theme discovery, activation, and asset loading are handled by
     * CoreServiceProvider::bootThemes(). This provider only adds
     * Inertia root view setup and Blade view composers for theme data.
     */
    public function boot(): void
    {
        // Set Inertia root view
        Inertia::setRootView('app');

        // Share theme data with Blade views (if any Blade templates are used)
        $this->shareThemeData();
    }

    /**
     * Share theme data with all Blade views.
     */
    protected function shareThemeData(): void
    {
        View::composer('*', function ($view) {
            try {
                $theme = Theme::active();
                if ($theme) {
                    $view->with('activeTheme', [
                        'name'  => $theme->name,
                        'slug'  => $theme->slug,
                        'assets' => [
                            'css' => $theme->asset('css/theme.css'),
                            'js'  => $theme->asset('js/theme.js'),
                        ],
                    ]);
                }
            } catch (\Exception $e) {
                // Ignore during install/migration
            }
        });
    }
}
