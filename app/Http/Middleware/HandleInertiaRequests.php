<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Vortex\Core\Models\Theme;
use Vortex\Core\Models\Currency;
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

        // Skip database queries when running in console (e.g., during migrations)
        if (app()->runningInConsole()) {
            return array_merge(parent::share($request), [
                'name' => config('app.name'),
                'quote' => ['message' => trim($message), 'author' => trim($author)],
                'auth' => [
                    'user' => null,
                ],
                'menu' => [
                    'admin' => [],
                    'shop' => [],
                ],
                'flash' => \Inertia\Inertia::always(function () use ($request) {
                    return [
                        'success' => null,
                        'error' => null,
                        'warning' => null,
                        'info' => null,
                        'redirect_url' => null,
                    ];
                }),
                'sidebarOpen' => true,
                'ziggy' => fn () => [
                    'location' => $request->url(),
                ],
            ]);
        }

        $menuService = app(MenuService::class);

        // Build menu trees with error handling
        $adminMenu = [];
        $shopMenu = [];
        
        try {
            $adminMenu = $menuService->buildTree('admin');
        } catch (\Exception $e) {
            // Silently fail during migration when table doesn't exist
        }
        
        try {
            $shopMenu = $menuService->buildTree('shop');
        } catch (\Exception $e) {
            // Silently fail during migration when table doesn't exist
        }

        return array_merge(parent::share($request), [
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'menu' => [
                'admin' => $adminMenu,
                'shop' => $shopMenu,
            ],
            'flash' => \Inertia\Inertia::always(function () use ($request) {
                return [
                    'success' => $request->session()->get('success'),
                    'error' => $request->session()->get('error'),
                    'warning' => $request->session()->get('warning'),
                    'info' => $request->session()->get('info'),
                    'redirect_url' => $request->session()->get('redirect_url'),
                    'payment_response' => $request->session()->get('payment_response'),
                ];
            }),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'ziggy' => fn () => [
                ...\Illuminate\Support\Facades\Route::current()->originalParameters(),
                'location' => $request->url(),
            ],
            // Currency configuration
            'currency' => function () use ($request) {
                // Only load currency for frontend routes
                if ($request->is('admin/*') || $request->is('admin')) {
                    return null;
                }
                
                try {
                    $currency = Currency::getDefault();
                    return $currency ? [
                        'code' => $currency->code,
                        'symbol' => $currency->symbol,
                        'symbolPosition' => $currency->symbol_position,
                        'decimalPlaces' => $currency->decimal_places,
                    ] : null;
                } catch (\Exception $e) {
                    return null;
                }
            },
        ]);
    }
}
