<?php

declare(strict_types=1);

namespace Cartxis\CMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\CMS\Models\Block;

class BlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blocks = [
            [
                'identifier' => 'homepage-hero',
                'title' => 'Homepage Hero Banner',
                'type' => 'banner',
                'content' => json_encode([
                    'image' => '/images/hero-banner.jpg',
                    'alt' => 'Welcome to our store',
                    'title' => 'Welcome to Cartxis',
                    'description' => 'Discover amazing products at unbeatable prices',
                    'cta_text' => 'Shop Now',
                    'cta_url' => '/shop',
                ]),
                'status' => 'active',
            ],
            [
                'identifier' => 'newsletter-signup',
                'title' => 'Newsletter Subscription',
                'type' => 'newsletter',
                'content' => json_encode([
                    'heading' => 'Subscribe to our Newsletter',
                    'description' => 'Get the latest updates on new products and upcoming sales',
                    'placeholder' => 'Enter your email address',
                    'button_text' => 'Subscribe',
                    'action' => '/newsletter/subscribe',
                ]),
                'status' => 'active',
            ],
            [
                'identifier' => 'summer-sale',
                'title' => 'Summer Sale Promotion',
                'type' => 'promotion',
                'content' => json_encode([
                    'heading' => 'Summer Sale',
                    'discount' => 'Up to 50% OFF',
                    'description' => 'Limited time offer on selected items',
                    'code' => 'SUMMER2025',
                ]),
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addMonths(2),
            ],
            [
                'identifier' => 'announcement-bar',
                'title' => 'Announcement Bar',
                'type' => 'html',
                'content' => '<div class="bg-blue-600 text-white text-center py-2">🎉 Free Shipping on Orders Over $50! <a href="/shipping-info" class="underline">Learn More</a></div>',
                'status' => 'active',
            ],
            [
                'identifier' => 'trust-badges',
                'title' => 'Trust Badges',
                'type' => 'html',
                'content' => '<div class="flex justify-center gap-8 py-6">
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z"/></svg>
                        <p>Secure Payment</p>
                    </div>
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z"/></svg>
                        <p>Fast Delivery</p>
                    </div>
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z"/></svg>
                        <p>Easy Returns</p>
                    </div>
                </div>',
                'status' => 'active',
            ],
            [
                'identifier' => 'featured-categories',
                'title' => 'Featured Categories',
                'type' => 'html',
                'content' => '<div class="grid grid-cols-3 gap-4">
                    <div class="category-card">
                        <img src="/images/electronics.jpg" alt="Electronics">
                        <h3>Electronics</h3>
                    </div>
                    <div class="category-card">
                        <img src="/images/fashion.jpg" alt="Fashion">
                        <h3>Fashion</h3>
                    </div>
                    <div class="category-card">
                        <img src="/images/home.jpg" alt="Home & Living">
                        <h3>Home & Living</h3>
                    </div>
                </div>',
                'status' => 'active',
            ],
            [
                'identifier' => 'black-friday',
                'title' => 'Black Friday Mega Sale',
                'type' => 'promotion',
                'content' => json_encode([
                    'heading' => 'BLACK FRIDAY',
                    'discount' => '70% OFF',
                    'description' => 'Biggest sale of the year! Shop now before items sell out',
                    'code' => 'BLACKFRIDAY',
                ]),
                'status' => 'inactive', // Not active yet
                'start_date' => now()->addMonths(5),
                'end_date' => now()->addMonths(5)->addDays(3),
            ],
        ];

        foreach ($blocks as $blockData) {
            Block::create($blockData);
        }

        $this->command->info('Created ' . count($blocks) . ' sample blocks');
    }
}
