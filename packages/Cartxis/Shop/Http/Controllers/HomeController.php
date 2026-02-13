<?php

namespace Cartxis\Shop\Http\Controllers;

use Cartxis\Shop\Services\HomeService;
use Cartxis\Core\Services\ThemeViewResolver;
use Cartxis\Core\Services\SettingService;
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
     * @var SettingService
     */
    protected $settingService;

    /**
     * Create a new controller instance.
     *
     * @param HomeService $homeService
     * @param ThemeViewResolver $themeResolver
     */
    public function __construct(HomeService $homeService, ThemeViewResolver $themeResolver, SettingService $settingService)
    {
        $this->homeService = $homeService;
        $this->themeResolver = $themeResolver;
        $this->settingService = $settingService;
    }

    /**
     * Display the homepage.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $data = $this->homeService->getHomepageData();
        
        // Use ThemeViewResolver to get correct view path
        return Inertia::render($this->themeResolver->resolve('Home/Index'), [
            // 'theme' prop is shared via HandleInertiaRequests middleware (flattened settings)
            'featuredProducts' => $data['featured_products'],
            'featuredCategories' => $data['categories'], // Renamed for clarity
            'newProducts' => $data['new_products'],
            'categories' => $data['categories'], // Keep for backward compatibility
            'cmsBlocks' => $data['blocks'] ?? [],
            'siteConfig' => [
                'name' => $this->settingService->get('site_name') ?? config('app.name', 'Cartxis'),
                'url' => config('app.url'),
                'description' => $this->settingService->get('site_tagline') ?? 'Your trusted e-commerce platform',
                'logo' => $this->settingService->get('site_logo') ?? null,
                'favicon' => $this->settingService->get('site_favicon') ?? null,
            ],
            'seo' => [
                'title' => config('app.name') . ' - ' . trans('shop::shop.homepage.title'),
                'description' => 'Welcome to our online store',
                'keywords' => 'ecommerce, shop, products',
            ],
        ]);
    }
}
