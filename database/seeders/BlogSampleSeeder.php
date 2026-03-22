<?php

declare(strict_types=1);

namespace Database\Seeders;

use Cartxis\Blog\Models\BlogCategory;
use Cartxis\Blog\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogSampleSeeder extends Seeder
{
    public function run(): void
    {
        $cat = BlogCategory::firstOrCreate(
            ['slug' => 'tech-ecommerce'],
            [
                'name'        => 'Tech & eCommerce',
                'status'      => 'active',
                'description' => 'Tips and insights on technology and e-commerce.',
            ]
        );

        $posts = [
            [
                'title'           => 'Getting Started with E-Commerce in 2025',
                'slug'            => 'getting-started-with-ecommerce-2025',
                'excerpt'         => 'A complete beginner\'s guide to launching your online store, from choosing the right platform to your first sale.',
                'content'         => '<p>Starting an e-commerce business has never been more accessible. In this guide we cover domain setup, payment gateways, and conversion best practices.</p><p>Whether you are selling physical products or digital goods, the fundamentals remain the same: know your customer, optimise your funnel, and iterate fast.</p>',
                'status'          => 'published',
                'published_at'    => now(),
                'blog_category_id'=> $cat->id,
                'created_by'      => 1,
                'view_count'      => 128,
            ],
            [
                'title'           => '10 Best Practices for Online Store Management',
                'slug'            => '10-best-practices-online-store-management',
                'excerpt'         => 'Keep your store running smoothly with these ten proven management practices that top merchants swear by.',
                'content'         => '<p>Running an online store involves juggling inventory, customer service, marketing, and logistics. Here are 10 best practices to keep everything under control.</p><ul><li>Automate repetitive tasks</li><li>Monitor key metrics daily</li><li>Respond to reviews promptly</li></ul>',
                'status'          => 'published',
                'published_at'    => now()->subDays(3),
                'blog_category_id'=> $cat->id,
                'created_by'      => 1,
                'view_count'      => 74,
            ],
            [
                'title'           => 'How to Boost Your Sales with SEO',
                'slug'            => 'boost-sales-with-seo',
                'excerpt'         => 'Learn actionable SEO strategies that drive organic traffic and convert visitors into paying customers.',
                'content'         => '<p>Search engine optimisation is one of the highest ROI channels for e-commerce. From keyword research to technical SEO, this article walks you through a step-by-step process.</p><p>Topics covered: on-page SEO, schema markup for products, backlink building, and Core Web Vitals.</p>',
                'status'          => 'published',
                'published_at'    => now()->subDays(7),
                'blog_category_id'=> $cat->id,
                'created_by'      => 1,
                'view_count'      => 211,
            ],
        ];

        foreach ($posts as $data) {
            BlogPost::firstOrCreate(['slug' => $data['slug']], $data);
        }

        $this->command->info('Blog category and 3 sample posts seeded.');
    }
}
