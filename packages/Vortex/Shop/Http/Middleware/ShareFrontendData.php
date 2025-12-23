<?php

namespace Vortex\Shop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Vortex\Core\Models\Theme;
use Vortex\Shop\Repositories\CategoryRepository;

class ShareFrontendData
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply to non-admin routes
        if ($request->is('admin/*') || $request->is('admin')) {
            return $next($request);
        }

        // Share theme data
        Inertia::share([
            'theme' => function () {
                try {
                    $theme = Theme::where('is_active', true)->first();
                    if ($theme) {
                        return [
                            'name' => $theme->name,
                            'slug' => $theme->slug,
                            'settings' => [
                                'primary_color' => $theme->getSetting('colors.primary') ?? '#3b82f6',
                                'secondary_color' => $theme->getSetting('colors.secondary') ?? '#8b5cf6',
                                'features' => [
                                    'sticky_header' => $theme->getSetting('features.sticky_header') ?? true,
                                    'back_to_top' => $theme->getSetting('features.back_to_top') ?? true,
                                    'wishlist' => $theme->getSetting('features.wishlist') ?? true,
                                ]
                            ]
                        ];
                    }
                } catch (\Exception $e) {
                    // Silent fail during migrations
                }
                return null;
            },
            
            'siteConfig' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'description' => 'Vortex E-commerce Platform',
            ],
            
            // Share categories for header navigation
            'categories' => function () {
                try {
                    return $this->categoryRepository->getRootCategories()
                        ->map(function ($category) {
                            return [
                                'id' => $category->id,
                                'name' => $category->name,
                                'slug' => $category->slug,
                                'description' => $category->description,
                                'image' => $category->image,
                            ];
                        });
                } catch (\Exception $e) {
                    // Silent fail during migrations
                }
                return [];
            },
        ]);

        return $next($request);
    }
}
