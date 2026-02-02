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
        $appUrl = config('app.url', 'http://localhost');
        
        // Default channel
        Channel::updateOrCreate(
            ['slug' => 'default-store'],
            [
                'name' => 'Default Store',
                'theme_id' => 1,
                'status' => 'active',
                'is_default' => true,
                'url' => $appUrl,
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
                'url' => str_replace(['http://', 'https://'], ['http://mobile.', 'https://mobile.'], $appUrl),
                'description' => 'Mobile-optimized storefront',
            ]
        );
    }
}
