<?php

namespace Cartxis\CMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StorefrontMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Header Menu Items
        $this->createHeaderMenu();
        
        // Create Footer Menu Items
        $this->createFooterMenu();
        
        // Create Mobile Menu Items
        $this->createMobileMenu();
    }

    private function createHeaderMenu(): void
    {
        // Create parent menu items for header
        $shopId = DB::table('menu_items')->insertGetId([
            'title' => 'Shop',
            'key' => 'shop',
            'icon' => 'shopping-bag',
            'route' => null,
            'url' => '/products',
            'location' => 'storefront',
            'menu_type' => 'header',
            'parent_id' => null,
            'order' => 1,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoriesId = DB::table('menu_items')->insertGetId([
            'title' => 'Categories',
            'key' => 'categories',
            'icon' => 'folder-tree',
            'route' => null,
            'url' => '#',
            'location' => 'storefront',
            'menu_type' => 'header',
            'parent_id' => null,
            'order' => 2,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add category sub-items (these would be dynamically loaded from actual categories)
        DB::table('menu_items')->insert([
            [
                'title' => 'Electronics',
                'key' => 'category-electronics',
                'icon' => null,
                'route' => null,
                'url' => '/category/electronics',
                'location' => 'storefront',
                'menu_type' => 'header',
                'parent_id' => $categoriesId,
                'order' => 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Clothing',
                'key' => 'category-clothing',
                'icon' => null,
                'route' => null,
                'url' => '/category/clothing',
                'location' => 'storefront',
                'menu_type' => 'header',
                'parent_id' => $categoriesId,
                'order' => 2,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Home & Garden',
                'key' => 'category-home',
                'icon' => null,
                'route' => null,
                'url' => '/category/home-garden',
                'location' => 'storefront',
                'menu_type' => 'header',
                'parent_id' => $categoriesId,
                'order' => 3,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('menu_items')->insert([
            [
                'title' => 'Deals',
                'key' => 'deals',
                'icon' => 'percent',
                'route' => null,
                'url' => '/deals',
                'location' => 'storefront',
                'menu_type' => 'header',
                'parent_id' => null,
                'order' => 3,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'About',
                'key' => 'about',
                'icon' => null,
                'route' => null,
                'url' => '/about',
                'location' => 'storefront',
                'menu_type' => 'header',
                'parent_id' => null,
                'order' => 4,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function createFooterMenu(): void
    {
        // Company section
        $companyId = DB::table('menu_items')->insertGetId([
            'title' => 'Company',
            'key' => 'footer-company',
            'icon' => null,
            'route' => null,
            'url' => '#',
            'location' => 'storefront',
            'menu_type' => 'footer',
            'parent_id' => null,
            'order' => 1,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('menu_items')->insert([
            [
                'title' => 'About Us',
                'key' => 'footer-about',
                'icon' => null,
                'route' => null,
                'url' => '/about',
                'location' => 'storefront',
                'menu_type' => 'footer',
                'parent_id' => $companyId,
                'order' => 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Careers',
                'key' => 'footer-careers',
                'icon' => null,
                'route' => null,
                'url' => '/careers',
                'location' => 'storefront',
                'menu_type' => 'footer',
                'parent_id' => $companyId,
                'order' => 2,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Contact',
                'key' => 'footer-contact',
                'icon' => null,
                'route' => null,
                'url' => '/contact',
                'location' => 'storefront',
                'menu_type' => 'footer',
                'parent_id' => $companyId,
                'order' => 3,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Customer Service section
        $serviceId = DB::table('menu_items')->insertGetId([
            'title' => 'Customer Service',
            'key' => 'footer-service',
            'icon' => null,
            'route' => null,
            'url' => '#',
            'location' => 'storefront',
            'menu_type' => 'footer',
            'parent_id' => null,
            'order' => 2,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('menu_items')->insert([
            [
                'title' => 'Help Center',
                'key' => 'footer-help',
                'icon' => null,
                'route' => null,
                'url' => '/help',
                'location' => 'storefront',
                'menu_type' => 'footer',
                'parent_id' => $serviceId,
                'order' => 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Shipping & Returns',
                'key' => 'footer-shipping',
                'icon' => null,
                'route' => null,
                'url' => '/shipping',
                'location' => 'storefront',
                'menu_type' => 'footer',
                'parent_id' => $serviceId,
                'order' => 2,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Track Order',
                'key' => 'footer-track',
                'icon' => null,
                'route' => null,
                'url' => '/track-order',
                'location' => 'storefront',
                'menu_type' => 'footer',
                'parent_id' => $serviceId,
                'order' => 3,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Legal section
        $legalId = DB::table('menu_items')->insertGetId([
            'title' => 'Legal',
            'key' => 'footer-legal',
            'icon' => null,
            'route' => null,
            'url' => '#',
            'location' => 'storefront',
            'menu_type' => 'footer',
            'parent_id' => null,
            'order' => 3,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('menu_items')->insert([
            [
                'title' => 'Privacy Policy',
                'key' => 'footer-privacy',
                'icon' => null,
                'route' => null,
                'url' => '/privacy',
                'location' => 'storefront',
                'menu_type' => 'footer',
                'parent_id' => $legalId,
                'order' => 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Terms of Service',
                'key' => 'footer-terms',
                'icon' => null,
                'route' => null,
                'url' => '/terms',
                'location' => 'storefront',
                'menu_type' => 'footer',
                'parent_id' => $legalId,
                'order' => 2,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function createMobileMenu(): void
    {
        // Mobile menu - simplified version
        DB::table('menu_items')->insert([
            [
                'title' => 'Shop All',
                'key' => 'mobile-shop',
                'icon' => 'shopping-bag',
                'route' => null,
                'url' => '/products',
                'location' => 'storefront',
                'menu_type' => 'mobile',
                'parent_id' => null,
                'order' => 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Deals',
                'key' => 'mobile-deals',
                'icon' => 'percent',
                'route' => null,
                'url' => '/deals',
                'location' => 'storefront',
                'menu_type' => 'mobile',
                'parent_id' => null,
                'order' => 2,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Account',
                'key' => 'mobile-account',
                'icon' => 'users',
                'route' => null,
                'url' => '/account',
                'location' => 'storefront',
                'menu_type' => 'mobile',
                'parent_id' => null,
                'order' => 3,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Help',
                'key' => 'mobile-help',
                'icon' => 'help-circle',
                'route' => null,
                'url' => '/help',
                'location' => 'storefront',
                'menu_type' => 'mobile',
                'parent_id' => null,
                'order' => 4,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
