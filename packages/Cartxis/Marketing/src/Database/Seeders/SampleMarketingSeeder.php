<?php

namespace Cartxis\Marketing\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\Marketing\Models\Coupon;
use Cartxis\Marketing\Models\Promotion;
use Carbon\Carbon;

class SampleMarketingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSampleCoupons();
        $this->createSamplePromotions();
    }

    /**
     * Create sample coupons.
     */
    protected function createSampleCoupons(): void
    {
        // 1. Percentage Discount Coupon
        Coupon::create([
            'code' => 'SAVE20',
            'name' => '20% Off All Orders',
            'description' => 'Get 20% off your entire order',
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => 20.00,
            'max_discount' => 100.00,
            'min_order_amount' => 50.00,
            'is_active' => true,
            'is_public' => true,
            'stackable' => true,
            'usage_limit_total' => 1000,
            'usage_limit_per_customer' => 1,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(30),
            'priority' => 10,
        ]);

        // 2. Fixed Amount Coupon
        Coupon::create([
            'code' => 'GET50OFF',
            'name' => '$50 Off Orders Over $200',
            'description' => 'Save $50 on orders over $200',
            'type' => Coupon::TYPE_FIXED_AMOUNT,
            'value' => 50.00,
            'min_order_amount' => 200.00,
            'is_active' => true,
            'is_public' => true,
            'stackable' => false,
            'usage_limit_total' => 500,
            'usage_limit_per_customer' => 3,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(60),
            'priority' => 20,
        ]);

        // 3. Free Shipping Coupon
        Coupon::create([
            'code' => 'FREESHIP',
            'name' => 'Free Shipping',
            'description' => 'Get free shipping on your order',
            'type' => Coupon::TYPE_FREE_SHIPPING,
            'value' => 0,
            'min_order_amount' => 25.00,
            'is_active' => true,
            'is_public' => true,
            'stackable' => true,
            'start_date' => Carbon::now(),
            'end_date' => null, // No end date
            'priority' => 5,
        ]);

        // 4. Buy X Get Y Coupon
        Coupon::create([
            'code' => 'BUY2GET1',
            'name' => 'Buy 2 Get 1 Free',
            'description' => 'Buy 2 items, get 1 free',
            'type' => Coupon::TYPE_BUY_X_GET_Y,
            'value' => 0,
            'buy_quantity' => 2,
            'get_quantity' => 1,
            'is_active' => true,
            'is_public' => true,
            'stackable' => false,
            'usage_limit_per_customer' => 5,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(14),
            'priority' => 15,
        ]);

        // 5. First Order Only Coupon
        Coupon::create([
            'code' => 'WELCOME15',
            'name' => 'Welcome Discount',
            'description' => '15% off your first order',
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => 15.00,
            'max_discount' => 50.00,
            'is_active' => true,
            'is_public' => true,
            'first_order_only' => true,
            'stackable' => false,
            'start_date' => Carbon::now(),
            'priority' => 25,
        ]);

        // 6. Weekend Only Coupon
        Coupon::create([
            'code' => 'WEEKEND25',
            'name' => 'Weekend Special',
            'description' => '25% off on weekends only',
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => 25.00,
            'max_discount' => 75.00,
            'is_active' => true,
            'is_public' => true,
            'days_of_week' => ['saturday', 'sunday'],
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(3),
            'priority' => 30,
        ]);
    }

    /**
     * Create sample promotions.
     */
    protected function createSamplePromotions(): void
    {
        // 1. Catalog Rule - Summer Sale
        Promotion::create([
            'name' => 'Summer Sale',
            'description' => '25% off all products',
            'type' => Promotion::TYPE_CATALOG_RULE,
            'discount_type' => Promotion::DISCOUNT_PERCENTAGE,
            'discount_value' => 25.00,
            'is_active' => true,
            'show_badge' => true,
            'badge_text' => '25% OFF',
            'badge_color' => '#ffffff',
            'badge_bg_color' => '#ff0000',
            'badge_position' => Promotion::BADGE_TOP_RIGHT,
            'priority' => 10,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(30),
        ]);

        // 2. Flash Sale
        Promotion::create([
            'name' => 'Flash Friday',
            'description' => '50% off for 24 hours!',
            'type' => Promotion::TYPE_FLASH_SALE,
            'discount_type' => Promotion::DISCOUNT_PERCENTAGE,
            'discount_value' => 50.00,
            'is_active' => false, // Activate manually
            'show_badge' => true,
            'badge_text' => 'FLASH 50%',
            'badge_color' => '#ffff00',
            'badge_bg_color' => '#ff0000',
            'badge_position' => Promotion::BADGE_TOP_LEFT,
            'show_countdown' => true,
            'usage_limit' => 100,
            'priority' => 50,
            'start_date' => Carbon::now()->addDays(7),
            'end_date' => Carbon::now()->addDays(8),
        ]);

        // 3. Cart Rule - Spend More Save More
        Promotion::create([
            'name' => 'Spend $100, Get $15 Off',
            'description' => 'Automatic discount on orders over $100',
            'type' => Promotion::TYPE_CART_RULE,
            'discount_type' => Promotion::DISCOUNT_FIXED_AMOUNT,
            'discount_value' => 15.00,
            'is_active' => true,
            'stackable_with_coupons' => true,
            'priority' => 20,
            'conditions' => [
                'min_order_amount' => 100,
            ],
            'start_date' => Carbon::now(),
        ]);

        // 4. Tiered Pricing
        Promotion::create([
            'name' => 'Bulk Discount',
            'description' => 'Buy more, save more',
            'type' => Promotion::TYPE_TIERED_PRICING,
            'discount_type' => Promotion::DISCOUNT_PERCENTAGE,
            'discount_value' => 0, // Set via tiers
            'is_active' => true,
            'priority' => 15,
            'price_tiers' => [
                ['min_quantity' => 5, 'max_quantity' => 9, 'discount_percentage' => 10],
                ['min_quantity' => 10, 'max_quantity' => 19, 'discount_percentage' => 15],
                ['min_quantity' => 20, 'max_quantity' => null, 'discount_percentage' => 20],
            ],
            'start_date' => Carbon::now(),
        ]);

        // 5. Bundle Deal (placeholder)
        Promotion::create([
            'name' => 'Bundle & Save',
            'description' => 'Buy product bundle, save $100',
            'type' => Promotion::TYPE_BUNDLE,
            'discount_type' => Promotion::DISCOUNT_FIXED_AMOUNT,
            'discount_value' => 100.00,
            'is_active' => true,
            'show_badge' => true,
            'badge_text' => 'BUNDLE DEAL',
            'badge_color' => '#ffffff',
            'badge_bg_color' => '#00aa00',
            'badge_position' => Promotion::BADGE_BOTTOM_RIGHT,
            'priority' => 25,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(45),
        ]);
    }
}
