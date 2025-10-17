<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            // Dashboard
            [
                'key' => 'dashboard',
                'title' => 'Dashboard',
                'icon' => 'layout-dashboard',
                'route' => 'admin.dashboard',
                'parent_id' => null,
                'order' => 10,
                'location' => 'admin',
                'active' => true,
            ],

            // Catalog Parent
            [
                'key' => 'catalog',
                'title' => 'Catalog',
                'icon' => 'shopping-bag',
                'route' => null,
                'parent_id' => null,
                'order' => 20,
                'location' => 'admin',
                'active' => true,
            ],

            // Sales Parent
            [
                'key' => 'sales',
                'title' => 'Sales',
                'icon' => 'shopping-cart',
                'route' => null,
                'parent_id' => null,
                'order' => 30,
                'location' => 'admin',
                'active' => true,
            ],

            // Customers Parent
            [
                'key' => 'customers',
                'title' => 'Customers',
                'icon' => 'users',
                'route' => null,
                'parent_id' => null,
                'order' => 40,
                'location' => 'admin',
                'active' => true,
            ],

            // Marketing Parent
            [
                'key' => 'marketing',
                'title' => 'Marketing',
                'icon' => 'trending-up',
                'route' => null,
                'parent_id' => null,
                'order' => 50,
                'location' => 'admin',
                'active' => true,
            ],

            // Content Parent
            [
                'key' => 'content',
                'title' => 'Content',
                'icon' => 'file-text',
                'route' => null,
                'parent_id' => null,
                'order' => 60,
                'location' => 'admin',
                'active' => true,
            ],

            // Reports Parent
            [
                'key' => 'reports',
                'title' => 'Reports',
                'icon' => 'bar-chart-3',
                'route' => null,
                'parent_id' => null,
                'order' => 70,
                'location' => 'admin',
                'active' => true,
            ],

            // System Parent
            [
                'key' => 'system',
                'title' => 'System',
                'icon' => 'settings',
                'route' => null,
                'parent_id' => null,
                'order' => 80,
                'location' => 'admin',
                'active' => true,
            ],
        ];

        // Insert parent menus first
        foreach ($menus as $menu) {
            DB::table('menu_items')->updateOrInsert(
                ['key' => $menu['key']],
                array_merge($menu, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Get parent IDs
        $catalogId = DB::table('menu_items')->where('key', 'catalog')->value('id');
        $salesId = DB::table('menu_items')->where('key', 'sales')->value('id');
        $customersId = DB::table('menu_items')->where('key', 'customers')->value('id');
        $marketingId = DB::table('menu_items')->where('key', 'marketing')->value('id');
        $contentId = DB::table('menu_items')->where('key', 'content')->value('id');
        $reportsId = DB::table('menu_items')->where('key', 'reports')->value('id');
        $systemId = DB::table('menu_items')->where('key', 'system')->value('id');

        // Child menu items
        $childMenus = [
            // Catalog Children
            [
                'key' => 'catalog-products',
                'title' => 'Products',
                'icon' => 'package',
                'route' => 'admin.catalog.products.index',
                'parent_id' => $catalogId,
                'order' => 1,
                'location' => 'admin',
                'active' => true,
            ],
            [
                'key' => 'catalog-categories',
                'title' => 'Categories',
                'icon' => 'folder-tree',
                'route' => 'admin.catalog.categories.index',
                'parent_id' => $catalogId,
                'order' => 2,
                'location' => 'admin',
                'active' => true,
            ],
            [
                'key' => 'catalog-attributes',
                'title' => 'Attributes',
                'icon' => 'list-checks',
                'route' => 'admin.catalog.attributes.index',
                'parent_id' => $catalogId,
                'order' => 3,
                'location' => 'admin',
                'active' => true,
            ],
            [
                'key' => 'catalog-brands',
                'title' => 'Brands',
                'icon' => 'tag',
                'route' => 'admin.catalog.brands.index',
                'parent_id' => $catalogId,
                'order' => 4,
                'location' => 'admin',
                'active' => true,
            ],
            [
                'key' => 'catalog-reviews',
                'title' => 'Reviews',
                'icon' => 'star',
                'route' => 'admin.catalog.reviews.index',
                'parent_id' => $catalogId,
                'order' => 5,
                'location' => 'admin',
                'active' => true,
            ],

            // Sales Children
            [
                'key' => 'sales-orders',
                'title' => 'Orders',
                'icon' => 'shopping-cart',
                'route' => null,
                'parent_id' => $salesId,
                'order' => 1,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'sales-invoices',
                'title' => 'Invoices',
                'icon' => 'receipt',
                'route' => null,
                'parent_id' => $salesId,
                'order' => 2,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'sales-shipments',
                'title' => 'Shipments',
                'icon' => 'truck',
                'route' => null,
                'parent_id' => $salesId,
                'order' => 3,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'sales-transactions',
                'title' => 'Transactions',
                'icon' => 'credit-card',
                'route' => null,
                'parent_id' => $salesId,
                'order' => 4,
                'location' => 'admin',
                'active' => false,
            ],

            // Customers Children
            [
                'key' => 'customers-all',
                'title' => 'All Customers',
                'icon' => 'users',
                'route' => null,
                'parent_id' => $customersId,
                'order' => 1,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'customers-groups',
                'title' => 'Customer Groups',
                'icon' => 'tag',
                'route' => null,
                'parent_id' => $customersId,
                'order' => 2,
                'location' => 'admin',
                'active' => false,
            ],

            // Marketing Children
            [
                'key' => 'marketing-promotions',
                'title' => 'Promotions',
                'icon' => 'megaphone',
                'route' => null,
                'parent_id' => $marketingId,
                'order' => 1,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'marketing-coupons',
                'title' => 'Coupons',
                'icon' => 'ticket',
                'route' => null,
                'parent_id' => $marketingId,
                'order' => 2,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'marketing-email',
                'title' => 'Email Campaigns',
                'icon' => 'mail',
                'route' => null,
                'parent_id' => $marketingId,
                'order' => 3,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'marketing-seo',
                'title' => 'SEO',
                'icon' => 'globe',
                'route' => null,
                'parent_id' => $marketingId,
                'order' => 4,
                'location' => 'admin',
                'active' => false,
            ],

            // Content Children
            [
                'key' => 'content-pages',
                'title' => 'Pages',
                'icon' => 'book-open',
                'route' => null,
                'parent_id' => $contentId,
                'order' => 1,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'content-blog',
                'title' => 'Blog Posts',
                'icon' => 'newspaper',
                'route' => null,
                'parent_id' => $contentId,
                'order' => 2,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'content-media',
                'title' => 'Media Gallery',
                'icon' => 'image',
                'route' => null,
                'parent_id' => $contentId,
                'order' => 3,
                'location' => 'admin',
                'active' => false,
            ],

            // Reports Children
            [
                'key' => 'reports-sales',
                'title' => 'Sales Reports',
                'icon' => 'trending-up',
                'route' => null,
                'parent_id' => $reportsId,
                'order' => 1,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'reports-products',
                'title' => 'Product Reports',
                'icon' => 'package',
                'route' => null,
                'parent_id' => $reportsId,
                'order' => 2,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'reports-customers',
                'title' => 'Customer Reports',
                'icon' => 'users',
                'route' => null,
                'parent_id' => $reportsId,
                'order' => 3,
                'location' => 'admin',
                'active' => false,
            ],

            // System Children
            [
                'key' => 'system-settings',
                'title' => 'Settings',
                'icon' => 'settings',
                'route' => null,
                'parent_id' => $systemId,
                'order' => 1,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'system-extensions',
                'title' => 'Extensions',
                'icon' => 'server',
                'route' => null,
                'parent_id' => $systemId,
                'order' => 2,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'system-permissions',
                'title' => 'Permissions',
                'icon' => 'shield',
                'route' => null,
                'parent_id' => $systemId,
                'order' => 3,
                'location' => 'admin',
                'active' => false,
            ],
            [
                'key' => 'system-maintenance',
                'title' => 'Maintenance',
                'icon' => 'wrench',
                'route' => null,
                'parent_id' => $systemId,
                'order' => 4,
                'location' => 'admin',
                'active' => false,
            ],
        ];

        // Insert child menus
        foreach ($childMenus as $menu) {
            DB::table('menu_items')->updateOrInsert(
                ['key' => $menu['key']],
                array_merge($menu, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✓ Admin menu items seeded successfully!');
    }
}
