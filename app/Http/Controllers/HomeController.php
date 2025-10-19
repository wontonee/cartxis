<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Packages\Core\Models\Theme;

class HomeController extends Controller
{
    /**
     * Display the homepage.
     */
    public function index(): Response
    {
        // Get active theme
        $theme = Theme::active();
        
        // Get featured categories (will be dynamic when Category module is ready)
        $featuredCategories = [
            [
                'id' => 1,
                'name' => 'Electronics',
                'slug' => 'electronics',
                'icon' => '💻',
                'description' => 'Latest gadgets and tech',
                'products_count' => 0,
                'image' => null
            ],
            [
                'id' => 2,
                'name' => 'Fashion',
                'slug' => 'fashion',
                'icon' => '👕',
                'description' => 'Trendy clothing and accessories',
                'products_count' => 0,
                'image' => null
            ],
            [
                'id' => 3,
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'icon' => '🏠',
                'description' => 'Furniture and decor',
                'products_count' => 0,
                'image' => null
            ],
            [
                'id' => 4,
                'name' => 'Sports',
                'slug' => 'sports',
                'icon' => '⚽',
                'description' => 'Equipment and gear',
                'products_count' => 0,
                'image' => null
            ]
        ];
        
        // Get featured products (will be dynamic when Product module is ready)
        $featuredProducts = [];
        
        // Platform status
        $platformStatus = [
            'ready' => [
                ['name' => 'Admin Panel', 'icon' => '✓'],
                ['name' => 'Theme System', 'icon' => '✓'],
                ['name' => 'Product Reviews Backend', 'icon' => '✓'],
                ['name' => 'Authentication', 'icon' => '✓']
            ],
            'coming_soon' => [
                ['name' => 'Product Catalog', 'icon' => '⏳'],
                ['name' => 'Shopping Cart', 'icon' => '⏳'],
                ['name' => 'Checkout System', 'icon' => '⏳'],
                ['name' => 'Payment Integration', 'icon' => '⏳']
            ]
        ];
        
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
        
        // Determine which view to use based on active theme
        $viewPath = $theme ? "themes/{$theme->slug}/pages/Home" : 'Home';
        
        return Inertia::render($viewPath, [
            'theme' => [
                'name' => $theme?->name ?? 'Default',
                'slug' => $theme?->slug ?? 'default',
                'settings' => $themeSettings
            ],
            'featuredCategories' => $featuredCategories,
            'featuredProducts' => $featuredProducts,
            'platformStatus' => $platformStatus,
            'siteConfig' => [
                'name' => config('app.name', 'Vortex'),
                'url' => config('app.url'),
                'description' => 'Your trusted e-commerce platform'
            ]
        ]);
    }
}
