<?php

namespace Cartxis\Core\Database\Seeders;

use Cartxis\Core\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default channel
        Channel::updateOrCreate(
            ['slug' => 'default-store'],
            [
                'name' => 'Default Store',
                'theme_id' => 1,
                'status' => 'active',
                'is_default' => true,
                'url' => 'http://cartxis.test',
                'description' => 'Main storefront channel',
            ]
        );

        // Optional: Additional channels for demo
        Channel::updateOrCreate(
            ['slug' => 'mobile-store'],
            [
                'name' => 'Mobile Store',
                'theme_id' => 1,
                'status' => 'active',
                'is_default' => false,
                'url' => 'http://mobile.cartxis.test',
                'description' => 'Mobile-optimized storefront',
            ]
        );
    }
}
