<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Vortex\Admin\Database\Seeders\AdminUserSeeder;
use Vortex\Admin\Database\Seeders\AdminMenuSeeder;
use Vortex\Product\Database\Seeders\CategorySeeder;
use Vortex\Product\Database\Seeders\BrandSeeder;
use Vortex\Product\Database\Seeders\BrandMenuSeeder;
use Vortex\Product\Database\Seeders\AttributeSeeder;
use Vortex\Product\Database\Seeders\ProductSeeder;
use Vortex\Product\Database\Seeders\ReviewSeeder;
use Vortex\Shop\Database\Seeders\ThemeSeeder;
use Vortex\Core\Database\Seeders\PaymentMethodsTableSeeder;
use Vortex\Core\Database\Seeders\ChannelSeeder;
use Vortex\Core\Database\Seeders\ShippingMethodSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Admin Package Seeders
            AdminUserSeeder::class,
            AdminMenuSeeder::class,

            // Product Package Seeders
            CategorySeeder::class,
            BrandSeeder::class,
            BrandMenuSeeder::class,
            AttributeSeeder::class,
            ProductSeeder::class,
            ReviewSeeder::class,

            // Shop Package Seeders
            ThemeSeeder::class,

            // Core Package Seeders
            PaymentMethodsTableSeeder::class,

            // Channel Seeder
            ChannelSeeder::class,

            // Shipping Methods Seeder
            ShippingMethodSeeder::class,
        ]);
    }
}
