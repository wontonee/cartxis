<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Packages\Core\Models\Theme;
use Vortex\Core\Services\MenuService;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the root view dynamically based on the route.
     */
    public function rootView(Request $request): string
    {
        // Admin routes use the admin template
        if ($request->is('admin/*') || $request->is('admin')) {
            return 'app';
        }

        // Frontend routes use the theme template (same app.blade.php for now)
        return 'app';
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $menuService = app(MenuService::class);

        // Load active theme for frontend routes
        $theme = null;
        $siteConfig = null;
        
        if (!$request->is('admin/*') && !$request->is('admin')) {
            $theme = Theme::where('is_active', true)->first();
            $siteConfig = [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'description' => 'Vortex E-commerce Platform',
            ];
        }

        return array_merge(parent::share($request), [
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'menu' => [
                'admin' => $menuService->buildTree('admin'),
                'shop' => $menuService->buildTree('shop'),
            ],
            'flash' => \Inertia\Inertia::always(function () use ($request) {
                return [
                    'success' => $request->session()->get('success'),
                    'error' => $request->session()->get('error'),
                    'warning' => $request->session()->get('warning'),
                    'info' => $request->session()->get('info'),
                ];
            }),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'ziggy' => fn () => [
                ...\Illuminate\Support\Facades\Route::current()->originalParameters(),
                'location' => $request->url(),
            ],
            // Theme and site config for frontend
            'theme' => $theme,
            'siteConfig' => $siteConfig,
        ]);
    }
}
