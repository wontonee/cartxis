<?php

namespace Vortex\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Vortex\Core\Models\TaxClass;
use Vortex\Core\Models\TaxRate;
use Vortex\Core\Models\TaxZone;
use Vortex\Core\Models\TaxZoneLocation;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Tax Classes
        $taxClasses = [
            ['code' => 'standard', 'name' => 'Standard Rate', 'description' => 'Standard tax rate for most products', 'is_default' => true],
            ['code' => 'reduced', 'name' => 'Reduced Rate', 'description' => 'Reduced tax rate for specific items', 'is_default' => false],
            ['code' => 'zero', 'name' => 'Zero Rate', 'description' => 'Zero tax rate (exempt items)', 'is_default' => false],
            ['code' => 'digital', 'name' => 'Digital Goods', 'description' => 'Tax rate for digital products and services', 'is_default' => false],
        ];

        foreach ($taxClasses as $class) {
            TaxClass::updateOrCreate(['code' => $class['code']], $class);
        }

        // Seed Tax Rates (percentage stored as decimal, so 8.25% = 8.25)
        $taxRates = [
            ['code' => 'us_sales_tax', 'name' => 'US Sales Tax', 'percentage' => 8.25, 'priority' => 1, 'is_compound' => false, 'is_active' => true],
            ['code' => 'vat_standard', 'name' => 'VAT Standard (20%)', 'percentage' => 20.00, 'priority' => 1, 'is_compound' => false, 'is_active' => true],
            ['code' => 'vat_reduced', 'name' => 'VAT Reduced (5%)', 'percentage' => 5.00, 'priority' => 1, 'is_compound' => false, 'is_active' => true],
            ['code' => 'gst', 'name' => 'GST (10%)', 'percentage' => 10.00, 'priority' => 1, 'is_compound' => false, 'is_active' => true],
            ['code' => 'gst_india', 'name' => 'GST India (18%)', 'percentage' => 18.00, 'priority' => 1, 'is_compound' => false, 'is_active' => true],
        ];

        foreach ($taxRates as $rate) {
            TaxRate::updateOrCreate(['code' => $rate['code']], $rate);
        }

        // Seed Tax Zones
        $taxZones = [
            ['code' => 'us', 'name' => 'United States', 'description' => 'Tax zone for United States', 'is_active' => true],
            ['code' => 'eu', 'name' => 'European Union', 'description' => 'Tax zone for European Union', 'is_active' => true],
            ['code' => 'uk', 'name' => 'United Kingdom', 'description' => 'Tax zone for United Kingdom', 'is_active' => true],
            ['code' => 'au', 'name' => 'Australia', 'description' => 'Tax zone for Australia', 'is_active' => true],
            ['code' => 'in', 'name' => 'India', 'description' => 'Tax zone for India', 'is_active' => true],
        ];

        foreach ($taxZones as $zone) {
            $taxZone = TaxZone::updateOrCreate(['code' => $zone['code']], $zone);

            // Add location for US zone
            if ($zone['code'] === 'us') {
                TaxZoneLocation::updateOrCreate(
                    ['tax_zone_id' => $taxZone->id, 'country_code' => 'US'],
                    ['country_code' => 'US']
                );
            }

            // Add location for EU zone (example: Germany, France)
            if ($zone['code'] === 'eu') {
                TaxZoneLocation::updateOrCreate(
                    ['tax_zone_id' => $taxZone->id, 'country_code' => 'DE'],
                    ['country_code' => 'DE']
                );
                TaxZoneLocation::updateOrCreate(
                    ['tax_zone_id' => $taxZone->id, 'country_code' => 'FR'],
                    ['country_code' => 'FR']
                );
            }

            // Add location for UK zone
            if ($zone['code'] === 'uk') {
                TaxZoneLocation::updateOrCreate(
                    ['tax_zone_id' => $taxZone->id, 'country_code' => 'GB'],
                    ['country_code' => 'GB']
                );
            }

            // Add location for Australia zone
            if ($zone['code'] === 'au') {
                TaxZoneLocation::updateOrCreate(
                    ['tax_zone_id' => $taxZone->id, 'country_code' => 'AU'],
                    ['country_code' => 'AU']
                );
            }

            // Add location for India zone
            if ($zone['code'] === 'in') {
                TaxZoneLocation::updateOrCreate(
                    ['tax_zone_id' => $taxZone->id, 'country_code' => 'IN'],
                    ['country_code' => 'IN']
                );
            }
        }
    }
}
