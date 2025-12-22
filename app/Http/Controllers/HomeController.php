<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Core\Models\Theme;
use Vortex\Product\Models\Category;

class HomeController extends Controller
{
    /**
     * Display the homepage.
     */
    public function index(): Response
    {
        // Get active theme
        $theme = Theme::active();
        
        // Get real categories from database
        $categories = Category::where('status', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order', 'asc')
            ->get(['id', 'name', 'slug', 'parent_id', 'image'])
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->image,
                    'children' => $category->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                        ];
                    })
                ];
            });
        
        // Get featured categories for homepage showcase
        $featuredCategories = $categories->take(4);
        
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
            'categories' => $categories,
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
