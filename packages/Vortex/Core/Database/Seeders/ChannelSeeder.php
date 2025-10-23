<?php

namespace Vortex\Core\Database\Seeders;

use Vortex\Core\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default channel
        Channel::create([
            'name' => 'Default Store',
            'slug' => 'default-store',
            'theme_id' => 1,
            'status' => 'active',
            'is_default' => true,
            'url' => 'https://vortex.test',
            'description' => 'Main storefront channel',
        ]);

        // Optional: Additional channels for demo
        Channel::create([
            'name' => 'Mobile Store',
            'slug' => 'mobile-store',
            'theme_id' => 1,
            'status' => 'active',
            'is_default' => false,
            'url' => 'https://mobile.vortex.test',
            'description' => 'Mobile-optimized storefront',
        ]);
    }
}
