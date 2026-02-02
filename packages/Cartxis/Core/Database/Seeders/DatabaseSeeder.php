<?php

declare(strict_types=1);

namespace Cartxis\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\Admin\Database\Seeders\AdminUserSeeder;
use Cartxis\Admin\Database\Seeders\AdminMenuSeeder;
use Cartxis\Shop\Database\Seeders\ThemeSeeder;
use Cartxis\Core\Database\Seeders\PaymentMethodsTableSeeder;
use Cartxis\Core\Database\Seeders\ChannelSeeder;
use Cartxis\Core\Database\Seeders\ShippingMethodSeeder;
use Cartxis\Core\Database\Seeders\TaxSeeder;
use Cartxis\Core\Database\Seeders\EmailSeeder;
use Cartxis\Customer\Database\Seeders\CustomerMenuSeeder;
use Cartxis\Customer\Database\Seeders\CustomerGroupSeeder;
use Cartxis\CMS\Database\Seeders\BlockSeeder;
use Cartxis\CMS\Database\Seeders\PageSeeder;
use Cartxis\CMS\Database\Seeders\StorefrontMenuSeeder;
use Cartxis\Marketing\Database\Seeders\MarketingMenuSeeder;
use Cartxis\Marketing\Database\Seeders\SampleMarketingSeeder;
use Cartxis\Reports\Database\Seeders\ReportsMenuSeeder;
use Cartxis\Sales\Database\Seeders\CreditMemoEmailTemplatesSeeder;
use Cartxis\Sales\Database\Seeders\ShipmentEmailTemplatesSeeder;
use Cartxis\Settings\Database\Seeders\AiSettingsSeeder;

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
            AiSettingsSeeder::class,

            // Customer Package Seeders
            CustomerMenuSeeder::class,
            CustomerGroupSeeder::class,

            // Product Package Seeders

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
