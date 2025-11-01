<?php

namespace Vortex\Customer\Database\Seeders;

use Illuminate\Database\Seeder;
use Vortex\Core\Models\MenuItem;

class CustomerMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create parent Customers menu item
        $customerMenu = MenuItem::firstOrCreate(
            [
                'key' => 'customers',
                'location' => 'admin',
            ],
            [
                'title' => 'Customers',
                'icon' => 'users',
                'route' => null,
                'url' => null,
                'parent_id' => null,
                'order' => 30, // After Dashboard and Catalog
                'permission' => null,
                'active' => true,
                'extension_code' => 'vortex_customer',
            ]
        );

        // Create child menu items
        $childItems = [
            [
                'key' => 'customers.all',
                'title' => 'All Customers',
                'icon' => 'user',
                'route' => 'admin.customers.index',
                'url' => null,
                'order' => 1,
            ],
            [
                'key' => 'customers.groups',
                'title' => 'Customer Groups',
                'icon' => 'users',
                'route' => 'admin.customer-groups.index',
                'url' => null,
                'order' => 2,
            ],
        ];

        foreach ($childItems as $item) {
            MenuItem::firstOrCreate(
                [
                    'key' => $item['key'],
                    'location' => 'admin',
                ],
                [
                    'title' => $item['title'],
                    'icon' => $item['icon'],
                    'route' => $item['route'],
                    'url' => $item['url'],
                    'parent_id' => $customerMenu->id,
                    'order' => $item['order'],
                    'permission' => null,
                    'active' => true,
                    'extension_code' => 'vortex_customer',
                ]
            );
        }
    }
}
