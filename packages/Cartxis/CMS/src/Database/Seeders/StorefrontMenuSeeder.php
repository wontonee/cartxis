<?php

namespace Cartxis\CMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StorefrontMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Uses upsert keyed on (key, location) so this seeder is safe to re-run.
     */
    public function run(): void
    {
        $this->createHeaderMenu();
        $this->createFooterMenu();
        $this->createMobileMenu();
    }

    /** Upsert a single menu record; returns its ID. */
    private function upsert(array $match, array $data): int
    {
        $existing = DB::table('menu_items')->where($match)->first();

        if ($existing) {
            DB::table('menu_items')
                ->where('id', $existing->id)
                ->update(array_merge($data, ['updated_at' => now()]));
            return $existing->id;
        }

        return DB::table('menu_items')->insertGetId(
            array_merge($match, $data, ['created_at' => now(), 'updated_at' => now()])
        );
    }

    private function createHeaderMenu(): void
    {
        $this->upsert(
            ['key' => 'shop', 'location' => 'storefront'],
            ['title' => 'Shop', 'icon' => 'shopping-bag', 'route' => null, 'url' => '/products', 'menu_type' => 'header', 'parent_id' => null, 'order' => 1, 'active' => true]
        );

        $categoriesId = $this->upsert(
            ['key' => 'categories', 'location' => 'storefront'],
            ['title' => 'Categories', 'icon' => 'folder-tree', 'route' => null, 'url' => '#', 'menu_type' => 'header', 'parent_id' => null, 'order' => 2, 'active' => true]
        );

        foreach ([
            ['key' => 'category-electronics', 'title' => 'Electronics',  'url' => '/category/electronics', 'order' => 1],
            ['key' => 'category-clothing',    'title' => 'Clothing',      'url' => '/category/clothing',    'order' => 2],
            ['key' => 'category-home',        'title' => 'Home & Garden', 'url' => '/category/home-garden', 'order' => 3],
        ] as $cat) {
            $this->upsert(
                ['key' => $cat['key'], 'location' => 'storefront'],
                ['title' => $cat['title'], 'icon' => null, 'route' => null, 'url' => $cat['url'], 'menu_type' => 'header', 'parent_id' => $categoriesId, 'order' => $cat['order'], 'active' => true]
            );
        }

        $this->upsert(
            ['key' => 'deals', 'location' => 'storefront'],
            ['title' => 'Deals', 'icon' => 'percent', 'route' => null, 'url' => '/deals', 'menu_type' => 'header', 'parent_id' => null, 'order' => 3, 'active' => true]
        );

        $this->upsert(
            ['key' => 'about', 'location' => 'storefront'],
            ['title' => 'About', 'icon' => null, 'route' => null, 'url' => '/about-us', 'menu_type' => 'header', 'parent_id' => null, 'order' => 4, 'active' => true]
        );
    }

    private function createFooterMenu(): void
    {
        $companyId = $this->upsert(
            ['key' => 'footer-company', 'location' => 'storefront'],
            ['title' => 'Company', 'icon' => null, 'route' => null, 'url' => '#', 'menu_type' => 'footer', 'parent_id' => null, 'order' => 1, 'active' => true]
        );

        foreach ([
            ['key' => 'footer-about',   'title' => 'About Us',   'url' => '/about-us',   'order' => 1],
            ['key' => 'footer-careers', 'title' => 'Careers',    'url' => '/careers',    'order' => 2],
            ['key' => 'footer-contact', 'title' => 'Contact Us', 'url' => '/contact-us', 'order' => 3],
        ] as $item) {
            $this->upsert(
                ['key' => $item['key'], 'location' => 'storefront'],
                ['title' => $item['title'], 'icon' => null, 'route' => null, 'url' => $item['url'], 'menu_type' => 'footer', 'parent_id' => $companyId, 'order' => $item['order'], 'active' => true]
            );
        }

        $serviceId = $this->upsert(
            ['key' => 'footer-service', 'location' => 'storefront'],
            ['title' => 'Customer Service', 'icon' => null, 'route' => null, 'url' => '#', 'menu_type' => 'footer', 'parent_id' => null, 'order' => 2, 'active' => true]
        );

        foreach ([
            ['key' => 'footer-help',     'title' => 'Help Center',        'url' => '/help',                  'order' => 1],
            ['key' => 'footer-shipping', 'title' => 'Shipping & Returns',  'url' => '/shipping-and-returns',  'order' => 2],
            ['key' => 'footer-track',    'title' => 'Track Order',         'url' => '/checkout/track-order',  'order' => 3],
            ['key' => 'footer-faq',      'title' => 'FAQ',                 'url' => '/faq',                   'order' => 4],
        ] as $item) {
            $this->upsert(
                ['key' => $item['key'], 'location' => 'storefront'],
                ['title' => $item['title'], 'icon' => null, 'route' => null, 'url' => $item['url'], 'menu_type' => 'footer', 'parent_id' => $serviceId, 'order' => $item['order'], 'active' => true]
            );
        }

        $legalId = $this->upsert(
            ['key' => 'footer-legal', 'location' => 'storefront'],
            ['title' => 'Legal', 'icon' => null, 'route' => null, 'url' => '#', 'menu_type' => 'footer', 'parent_id' => null, 'order' => 3, 'active' => true]
        );

        foreach ([
            ['key' => 'footer-privacy', 'title' => 'Privacy Policy',  'url' => '/privacy-policy',       'order' => 1],
            ['key' => 'footer-terms',   'title' => 'Terms of Service', 'url' => '/terms-and-conditions', 'order' => 2],
        ] as $item) {
            $this->upsert(
                ['key' => $item['key'], 'location' => 'storefront'],
                ['title' => $item['title'], 'icon' => null, 'route' => null, 'url' => $item['url'], 'menu_type' => 'footer', 'parent_id' => $legalId, 'order' => $item['order'], 'active' => true]
            );
        }
    }

    private function createMobileMenu(): void
    {
        foreach ([
            ['key' => 'mobile-shop',    'title' => 'Shop All', 'icon' => 'shopping-bag', 'url' => '/products', 'order' => 1],
            ['key' => 'mobile-deals',   'title' => 'Deals',    'icon' => 'percent',      'url' => '/deals',    'order' => 2],
            ['key' => 'mobile-account', 'title' => 'Account',  'icon' => 'users',        'url' => '/account',  'order' => 3],
            ['key' => 'mobile-help',    'title' => 'Help',     'icon' => 'help-circle',  'url' => '/help',     'order' => 4],
        ] as $item) {
            $this->upsert(
                ['key' => $item['key'], 'location' => 'storefront'],
                ['title' => $item['title'], 'icon' => $item['icon'], 'route' => null, 'url' => $item['url'], 'menu_type' => 'mobile', 'parent_id' => null, 'order' => $item['order'], 'active' => true]
            );
        }
    }
}
