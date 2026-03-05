<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\UIEditor\Models\SavedBlock;

class UIEditorSnippetsSeeder extends Seeder
{
    public function run(): void
    {
        // Wipe existing built-ins so re-running is safe (idempotent)
        SavedBlock::where('is_builtin', true)->delete();

        $snippets = [
            [
                'name'        => 'Hero — Dark',
                'description' => 'Full-width dark hero with CTA',
                'type'        => 'section',
                'layout_data' => [
                    'id' => 'snippet_hero_dark', 'type' => 'section',
                    'settings' => ['background_color' => '#0f172a', 'padding_top' => 80, 'padding_bottom' => 80, 'full_width' => true],
                    'columns'  => [[
                        'id' => 'col1', 'width' => 12, 'settings' => [], 'blocks' => [[
                            'id' => 'b1', 'type' => 'hero',
                            'settings' => ['headline' => 'Welcome Back', 'subheading' => 'Discover new arrivals and exclusive deals.', 'cta_text' => 'Shop Now', 'cta_url' => '/products', 'height' => 480],
                        ]],
                    ]],
                ],
            ],
            [
                'name'        => 'Features — 4 Column',
                'description' => 'Four icon boxes in a row',
                'type'        => 'section',
                'layout_data' => [
                    'id' => 'snippet_features_4col', 'type' => 'section',
                    'settings' => ['background_color' => '#f8fafc', 'padding_top' => 48, 'padding_bottom' => 48, 'full_width' => false],
                    'columns'  => [
                        ['id' => 'c1', 'width' => 3, 'settings' => [], 'blocks' => [['id' => 'b1', 'type' => 'icon_box', 'settings' => ['icon' => 'truck',      'icon_color' => '#2563eb', 'title' => 'Free Shipping',   'description' => 'On orders over $50.',           'align' => 'center']]]],
                        ['id' => 'c2', 'width' => 3, 'settings' => [], 'blocks' => [['id' => 'b2', 'type' => 'icon_box', 'settings' => ['icon' => 'shield',     'icon_color' => '#16a34a', 'title' => 'Secure Payment', 'description' => '100% encrypted.',             'align' => 'center']]]],
                        ['id' => 'c3', 'width' => 3, 'settings' => [], 'blocks' => [['id' => 'b3', 'type' => 'icon_box', 'settings' => ['icon' => 'refresh-cw', 'icon_color' => '#d97706', 'title' => 'Easy Returns',   'description' => '30-day hassle-free returns.',  'align' => 'center']]]],
                        ['id' => 'c4', 'width' => 3, 'settings' => [], 'blocks' => [['id' => 'b4', 'type' => 'icon_box', 'settings' => ['icon' => 'headphones', 'icon_color' => '#7c3aed', 'title' => '24/7 Support',   'description' => 'Always here to help.',         'align' => 'center']]]],
                    ],
                ],
            ],
            [
                'name'        => 'Newsletter — Split',
                'description' => 'Dark newsletter with email form',
                'type'        => 'section',
                'layout_data' => [
                    'id' => 'snippet_newsletter', 'type' => 'section',
                    'settings' => ['background_color' => '#0f172a', 'padding_top' => 0, 'padding_bottom' => 0, 'full_width' => true],
                    'columns'  => [[
                        'id' => 'col1', 'width' => 12, 'settings' => [], 'blocks' => [[
                            'id' => 'b1', 'type' => 'newsletter',
                            'settings' => ['bg_color' => '#0f172a', 'layout' => 'split', 'title' => 'Join Thousands of Happy Shoppers', 'cta_text' => 'Subscribe Free'],
                        ]],
                    ]],
                ],
            ],
            [
                'name'        => 'Testimonials — 3 Column',
                'description' => 'Three customer review cards',
                'type'        => 'section',
                'layout_data' => [
                    'id' => 'snippet_testimonials', 'type' => 'section',
                    'settings' => ['background_color' => '#ffffff', 'padding_top' => 64, 'padding_bottom' => 64, 'full_width' => false],
                    'columns'  => [[
                        'id' => 'col1', 'width' => 12, 'settings' => [], 'blocks' => [
                            ['id' => 'b1', 'type' => 'heading',      'settings' => ['level' => 'h2', 'text' => 'What Our Customers Say', 'align' => 'center', 'color' => '#111827']],
                            ['id' => 'b2', 'type' => 'spacer',       'settings' => ['height' => 32]],
                            ['id' => 'b3', 'type' => 'testimonials', 'settings' => ['items' => [
                                ['author' => 'Sarah M.', 'text' => 'Absolutely love this store! Outstanding quality and lightning-fast delivery.', 'rating' => 5],
                                ['author' => 'James K.', 'text' => "Best online shopping experience I've had. Accurate descriptions, top notch service.", 'rating' => 5],
                                ['author' => 'Emily R.', 'text' => 'Great prices, huge selection, and beautiful packaging. Highly recommended!', 'rating' => 5],
                            ]]],
                        ],
                    ]],
                ],
            ],
            [
                'name'        => 'CTA — Centered Banner',
                'description' => 'Blue centred call-to-action',
                'type'        => 'section',
                'layout_data' => [
                    'id' => 'snippet_cta', 'type' => 'section',
                    'settings' => ['background_color' => '#2563eb', 'padding_top' => 64, 'padding_bottom' => 64, 'full_width' => false],
                    'columns'  => [[
                        'id' => 'col1', 'width' => 12, 'settings' => [], 'blocks' => [
                            ['id' => 'b1', 'type' => 'heading', 'settings' => ['level' => 'h2', 'text' => 'Ready to Shop?', 'align' => 'center', 'color' => '#ffffff']],
                            ['id' => 'b2', 'type' => 'text',    'settings' => ['content' => '<p style="text-align:center;color:rgba(255,255,255,0.8)">Browse thousands of products at unbeatable prices.</p>']],
                            ['id' => 'b3', 'type' => 'spacer',  'settings' => ['height' => 16]],
                            ['id' => 'b4', 'type' => 'button',  'settings' => ['text' => 'Shop Now', 'url' => '/products', 'align' => 'center', 'style' => 'solid', 'bg_color' => '#ffffff', 'text_color' => '#1d4ed8']],
                        ],
                    ]],
                ],
            ],
            [
                'name'        => 'Why Us — Dark 3 Column',
                'description' => 'Dark section with benefit icons',
                'type'        => 'section',
                'layout_data' => [
                    'id' => 'snippet_why_us', 'type' => 'section',
                    'settings' => ['background_color' => '#0f172a', 'padding_top' => 64, 'padding_bottom' => 64, 'full_width' => false],
                    'columns'  => [
                        ['id' => 'c1', 'width' => 4, 'settings' => [], 'blocks' => [['id' => 'b1', 'type' => 'icon_box', 'settings' => ['icon' => 'star',  'icon_color' => '#f59e0b', 'icon_size' => 40, 'title' => 'Premium Quality', 'description' => 'Every item is quality-checked before it ships.',   'align' => 'center', 'title_color' => '#ffffff', 'desc_color' => 'rgba(255,255,255,0.55)']]]],
                        ['id' => 'c2', 'width' => 4, 'settings' => [], 'blocks' => [['id' => 'b2', 'type' => 'icon_box', 'settings' => ['icon' => 'zap',   'icon_color' => '#38bdf8', 'icon_size' => 40, 'title' => 'Fast Delivery',   'description' => 'Same-day shipping on orders placed before 12pm.', 'align' => 'center', 'title_color' => '#ffffff', 'desc_color' => 'rgba(255,255,255,0.55)']]]],
                        ['id' => 'c3', 'width' => 4, 'settings' => [], 'blocks' => [['id' => 'b3', 'type' => 'icon_box', 'settings' => ['icon' => 'heart', 'icon_color' => '#f43f5e', 'icon_size' => 40, 'title' => 'Customer Love',  'description' => 'Over 50,000 happy customers and counting.',      'align' => 'center', 'title_color' => '#ffffff', 'desc_color' => 'rgba(255,255,255,0.55)']]]],
                    ],
                ],
            ],
        ];

        foreach ($snippets as $snippet) {
            SavedBlock::create(array_merge($snippet, ['is_builtin' => true, 'created_by' => null]));
        }
    }
}
