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
        $customerMenu = MenuItem::updateOrCreate(
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
                'order' => 40,
                'permission' => null,
                'active' => true,
                'extension_code' => 'vortex_customer',
            ]
        );

        // Create child menu items
        $childItems = [
            [
                'key' => 'customers-all',
                'title' => 'All Customers',
                'icon' => 'user',
                'route' => 'admin.customers.index',
                'url' => null,
                'order' => 10,
                'permission' => null,
            ],
            [
                'key' => 'customers-groups',
                'title' => 'Customer Groups',
                'icon' => 'users',
                'route' => 'admin.customers.groups.index',
                'url' => null,
                'order' => 20,
                'permission' => null,
            ],
        ];

        foreach ($childItems as $item) {
            MenuItem::updateOrCreate(
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
                    'permission' => $item['permission'],
                    'active' => true,
                    'extension_code' => 'vortex_customer',
                ]
            );
        }

        $this->command->info('✓ Customer menu items seeded successfully!');
    }
}
