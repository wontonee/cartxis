<?php

namespace Cartxis\Marketing\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\Core\Models\MenuItem;

class MarketingMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create parent Marketing menu item
        $marketingMenu = MenuItem::updateOrCreate(
            [
                'key' => 'marketing',
                'location' => 'admin',
            ],
            [
                'title' => 'Marketing',
                'icon' => 'megaphone',
                'route' => null,
                'url' => null,
                'parent_id' => null,
                'order' => 50,
                'permission' => null,
                'active' => true,
                'extension_code' => 'vortex_marketing',
            ]
        );

        // Create child menu items
        $childItems = [
            [
                'key' => 'marketing-coupons',
                'title' => 'Coupons',
                'icon' => 'ticket',
                'route' => 'admin.marketing.coupons.index',
                'url' => null,
                'order' => 10,
                'permission' => null,
            ],
            [
                'key' => 'marketing-promotions',
                'title' => 'Promotions',
                'icon' => 'sparkles',
                'route' => 'admin.marketing.promotions.index',
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
                    'parent_id' => $marketingMenu->id,
                    'order' => $item['order'],
                    'permission' => $item['permission'],
                    'active' => true,
                    'extension_code' => 'vortex_marketing',
                ]
            );
        }

        $this->command->info('✓ Marketing menu items seeded successfully!');
    }
}
