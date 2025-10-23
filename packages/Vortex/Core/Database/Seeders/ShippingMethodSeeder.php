<?php

namespace Vortex\Core\Database\Seeders;

use Vortex\Core\Models\ShippingMethod;
use Vortex\Core\Models\ShippingRate;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create flat-rate method (default)
        $flatRate = ShippingMethod::create([
            'name' => 'Standard Shipping',
            'slug' => 'standard-shipping',
            'type' => 'flat-rate',
            'base_cost' => 5.00,
            'cost_per_kg' => 0.5,
            'description' => 'Flat rate shipping: $5.00 + $0.50 per kg',
            'is_default' => true,
            'status' => 'active',
            'display_order' => 1,
        ]);

        // Create weight-based calculated method
        $calculated = ShippingMethod::create([
            'name' => 'Express Shipping',
            'slug' => 'express-shipping',
            'type' => 'calculated',
            'base_cost' => 0,
            'cost_per_kg' => 0,
            'description' => 'Weight-based shipping with rates by country and weight range',
            'is_default' => false,
            'status' => 'active',
            'display_order' => 2,
        ]);

        // Add rates for calculated method
        // US rates
        ShippingRate::create([
            'shipping_method_id' => $calculated->id,
            'country' => 'US',
            'state' => null,
            'min_weight' => 0,
            'max_weight' => 5,
            'base_cost' => 10.00,
            'cost_per_kg' => 1.00,
            'status' => 'active',
        ]);

        ShippingRate::create([
            'shipping_method_id' => $calculated->id,
            'country' => 'US',
            'state' => null,
            'min_weight' => 5,
            'max_weight' => 25,
            'base_cost' => 15.00,
            'cost_per_kg' => 0.75,
            'status' => 'active',
        ]);

        ShippingRate::create([
            'shipping_method_id' => $calculated->id,
            'country' => 'US',
            'state' => null,
            'min_weight' => 25,
            'max_weight' => 100,
            'base_cost' => 25.00,
            'cost_per_kg' => 0.50,
            'status' => 'active',
        ]);

        // Canada rates
        ShippingRate::create([
            'shipping_method_id' => $calculated->id,
            'country' => 'CA',
            'state' => null,
            'min_weight' => 0,
            'max_weight' => 5,
            'base_cost' => 15.00,
            'cost_per_kg' => 1.25,
            'status' => 'active',
        ]);

        ShippingRate::create([
            'shipping_method_id' => $calculated->id,
            'country' => 'CA',
            'state' => null,
            'min_weight' => 5,
            'max_weight' => 100,
            'base_cost' => 20.00,
            'cost_per_kg' => 0.90,
            'status' => 'active',
        ]);

        // UK rates
        ShippingRate::create([
            'shipping_method_id' => $calculated->id,
            'country' => 'GB',
            'state' => null,
            'min_weight' => 0,
            'max_weight' => 100,
            'base_cost' => 12.00,
            'cost_per_kg' => 0.80,
            'status' => 'active',
        ]);

        // Create basic local pickup option
        ShippingMethod::create([
            'name' => 'Local Pickup',
            'slug' => 'local-pickup',
            'type' => 'flat-rate',
            'base_cost' => 0,
            'cost_per_kg' => 0,
            'description' => 'Free local pickup option',
            'is_default' => false,
            'status' => 'active',
            'display_order' => 3,
        ]);
    }
}
