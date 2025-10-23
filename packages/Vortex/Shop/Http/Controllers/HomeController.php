<?php

namespace Vortex\Shop\Http\Controllers;

use Vortex\Shop\Services\HomeService;
use Vortex\Core\Services\ThemeViewResolver;
use Vortex\Core\Models\Theme;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * @var HomeService
     */
    protected $homeService;
    
    /**
     * @var ThemeViewResolver
     */
    protected $themeResolver;

    /**
     * Create a new controller instance.
     *
     * @param HomeService $homeService
     * @param ThemeViewResolver $themeResolver
     */
    public function __construct(HomeService $homeService, ThemeViewResolver $themeResolver)
    {
        $this->homeService = $homeService;
        $this->themeResolver = $themeResolver;
    }

    /**
     * Display the homepage.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $data = $this->homeService->getHomepageData();
        
        // Get active theme
        $theme = Theme::active();
        
        // Get theme settings
        $themeSettings = [
            'primary_color' => $theme?->getSetting('colors.primary') ?? '#3b82f6',
            'secondary_color' => $theme?->getSetting('colors.secondary') ?? '#8b5cf6',
            'features' => [
                'sticky_header' => $theme?->getSetting('features.sticky_header') ?? true,
                'back_to_top' => $theme?->getSetting('features.back_to_top') ?? true,
                'wishlist' => $theme?->getSetting('features.wishlist') ?? true,
            ]
        ];
        
        // Use ThemeViewResolver to get correct view path
        return Inertia::render($this->themeResolver->resolve('Home/Index'), [
            'theme' => [
                'name' => $theme?->name ?? 'Default',
                'slug' => $theme?->slug ?? 'default',
                'settings' => $themeSettings
            ],
            'featuredProducts' => $data['featured_products'],
            'newProducts' => $data['new_products'],
            'categories' => $data['categories'],
            'siteConfig' => [
                'name' => config('app.name', 'Vortex'),
                'url' => config('app.url'),
                'description' => 'Your trusted e-commerce platform'
            ],
            'seo' => [
                'title' => config('app.name') . ' - ' . trans('shop::shop.homepage.title'),
                'description' => 'Welcome to our online store',
                'keywords' => 'ecommerce, shop, products',
            ],
        ]);
    }
}
