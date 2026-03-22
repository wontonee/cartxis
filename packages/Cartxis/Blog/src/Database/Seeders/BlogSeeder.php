<?php

declare(strict_types=1);

namespace Cartxis\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\Blog\Models\BlogCategory;
use Cartxis\Blog\Models\BlogPost;
use App\Models\User;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $authorId = $admin?->id ?? 1;

        // Categories
        $news = BlogCategory::firstOrCreate(
            ['slug' => 'news'],
            ['name' => 'News', 'description' => 'The latest news and updates.', 'status' => 'active']
        );

        $tutorials = BlogCategory::firstOrCreate(
            ['slug' => 'tutorials'],
            ['name' => 'Tutorials', 'description' => 'Step-by-step guides and how-tos.', 'status' => 'active']
        );

        // Posts
        BlogPost::firstOrCreate(['slug' => 'welcome-to-our-blog'], [
            'title' => 'Welcome to Our Blog',
            'slug' => 'welcome-to-our-blog',
            'excerpt' => 'We are excited to launch our new blog. Stay tuned for updates, tutorials, and more.',
            'content' => '<p>Welcome to our brand new blog! We are thrilled to have you here. In the coming weeks, we will be publishing a variety of content including product updates, tutorials, and industry news.</p><p>Stay tuned and make sure to check back regularly for the latest posts.</p>',
            'status' => 'published',
            'published_at' => now(),
            'blog_category_id' => $news->id,
            'author_id' => $authorId,
            'created_by' => $authorId,
            'updated_by' => $authorId,
            'meta_title' => 'Welcome to Our Blog',
            'meta_description' => 'We are excited to launch our new blog. Stay tuned for updates, tutorials, and more.',
        ]);

        BlogPost::firstOrCreate(['slug' => 'getting-started-quick-guide'], [
            'title' => 'Getting Started: A Quick Guide',
            'slug' => 'getting-started-quick-guide',
            'excerpt' => 'Everything you need to know to get started with our platform in just a few easy steps.',
            'content' => '<h2>Step 1: Create an Account</h2><p>Sign up for a free account to access all features of the platform.</p><h2>Step 2: Explore the Dashboard</h2><p>Familiarise yourself with the admin dashboard. You will find all your tools and settings here.</p><h2>Step 3: Add Your First Product</h2><p>Navigate to the Products section and click <strong>Add New</strong> to list your first item.</p><p>That is all it takes to get started. If you need help at any point, our support team is just a message away.</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'blog_category_id' => $tutorials->id,
            'author_id' => $authorId,
            'created_by' => $authorId,
            'updated_by' => $authorId,
            'meta_title' => 'Getting Started: A Quick Guide',
            'meta_description' => 'Everything you need to know to get started with our platform in just a few easy steps.',
        ]);

        BlogPost::firstOrCreate(['slug' => 'top-tips-growing-online-store'], [
            'title' => 'Top Tips for Growing Your Online Store',
            'slug' => 'top-tips-growing-online-store',
            'excerpt' => 'Discover proven strategies to attract more customers and increase your sales.',
            'content' => '<p>Running a successful online store takes more than just having great products. Here are some tips to help you grow:</p><ul><li><strong>Optimise your product pages</strong> — clear titles, high-quality images, and detailed descriptions go a long way.</li><li><strong>Leverage email marketing</strong> — keep your customers informed about new arrivals and promotions.</li><li><strong>Use social proof</strong> — customer reviews and testimonials build trust.</li><li><strong>Focus on SEO</strong> — make sure your store ranks well on search engines.</li></ul><p>Implement these strategies consistently and you will see steady growth over time.</p>',
            'status' => 'published',
            'published_at' => now()->subDays(3),
            'blog_category_id' => $tutorials->id,
            'author_id' => $authorId,
            'created_by' => $authorId,
            'updated_by' => $authorId,
            'meta_title' => 'Top Tips for Growing Your Online Store',
            'meta_description' => 'Discover proven strategies to attract more customers and increase your sales.',
        ]);
    }
}
