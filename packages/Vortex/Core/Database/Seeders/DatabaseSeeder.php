<?php

declare(strict_types=1);

namespace Vortex\Core\Database\Seeders;

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
use Vortex\Core\Database\Seeders\TaxSeeder;
use Vortex\Core\Database\Seeders\EmailSeeder;
use Vortex\Customer\Database\Seeders\CustomerMenuSeeder;
use Vortex\Customer\Database\Seeders\CustomerGroupSeeder;
use Vortex\Customer\Database\Seeders\CustomerSeeder;
use Vortex\CMS\Database\Seeders\BlockSeeder;
use Vortex\CMS\Database\Seeders\PageSeeder;
use Vortex\CMS\Database\Seeders\StorefrontMenuSeeder;
use Vortex\Marketing\Database\Seeders\MarketingMenuSeeder;
use Vortex\Marketing\Database\Seeders\SampleMarketingSeeder;
use Vortex\Reports\Database\Seeders\ReportsMenuSeeder;
use Vortex\Sales\Database\Seeders\CreditMemoEmailTemplatesSeeder;
use Vortex\Sales\Database\Seeders\ShipmentEmailTemplatesSeeder;

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

            // Core Package Seeders
            ChannelSeeder::class,
            PaymentMethodsTableSeeder::class,
            ShippingMethodSeeder::class,
            TaxSeeder::class,
            EmailSeeder::class,

            // Customer Package Seeders
            CustomerMenuSeeder::class,
            CustomerGroupSeeder::class,
            CustomerSeeder::class,

            // Product Package Seeders
            CategorySeeder::class,
            BrandSeeder::class,
            BrandMenuSeeder::class,
            AttributeSeeder::class,
            ProductSeeder::class,
            ReviewSeeder::class,

            // CMS Package Seeders
            BlockSeeder::class,
            PageSeeder::class,
            StorefrontMenuSeeder::class,

            // Marketing Package Seeders
            MarketingMenuSeeder::class,
            SampleMarketingSeeder::class,

            // Sales Package Seeders
            CreditMemoEmailTemplatesSeeder::class,
            ShipmentEmailTemplatesSeeder::class,

            // Reports Package Seeders
            ReportsMenuSeeder::class,

            // Shop Package Seeders
            ThemeSeeder::class,
        ]);
    }
}
