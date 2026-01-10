<?php

declare(strict_types=1);

namespace Vortex\Setup\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RetailDemoSeeder extends Seeder
{
    /**
     * Run the database seeds for Retail business type
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedBrands();
        $this->seedProducts();
        $this->seedPages();
        $this->seedBlocks();
    }

    /**
     * Download image from URL and store locally
     */
    private function downloadImage(string $url, string $filename): ?string
    {
        try {
            $imageContent = @file_get_contents($url);
            if ($imageContent === false) {
                return null;
            }

            $path = 'products/' . $filename;
            Storage::disk('public')->put($path, $imageContent);
            
            return $path;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function seedCategories(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Latest electronics and gadgets',
                'status' => 'enabled',
            ],
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Fashion and apparel for all',
                'status' => 'enabled',
            ],
            [
                'name' => 'Home & Living',
                'slug' => 'home-living',
                'description' => 'Home decor and essentials',
                'status' => 'enabled',
            ],
            [
                'name' => 'Sports & Outdoors',
                'slug' => 'sports-outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'status' => 'enabled',
            ],
            [
                'name' => 'Books & Media',
                'slug' => 'books-media',
                'description' => 'Books, movies, and music',
                'status' => 'enabled',
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insertOrIgnore([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description'],
                'status' => $category['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedBrands(): void
    {
        $brands = [
            ['name' => 'TechPro', 'slug' => 'techpro', 'is_featured' => true],
            ['name' => 'StyleHub', 'slug' => 'stylehub', 'is_featured' => true],
            ['name' => 'HomeComfort', 'slug' => 'homecomfort', 'is_featured' => true],
            ['name' => 'FitGear', 'slug' => 'fitgear', 'is_featured' => false],
            ['name' => 'ReadMore', 'slug' => 'readmore', 'is_featured' => false],
        ];

        foreach ($brands as $brand) {
            DB::table('brands')->insertOrIgnore([
                'name' => $brand['name'],
                'slug' => $brand['slug'],
                'is_featured' => $brand['is_featured'],
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedProducts(): void
    {
        $products = [
            // Electronics
            [
                'name' => 'Wireless Bluetooth Headphones',
                'sku' => 'TECH-HP-001',
                'price' => 79.99,
                'category' => 'electronics',
                'brand' => 'techpro',
                'description' => 'Premium wireless headphones with active noise cancellation, 30-hour battery life, and superior sound quality. Perfect for music lovers and travelers.',
                'short_description' => 'High-quality wireless headphones with noise cancellation',
                'featured' => true,
                'quantity' => 50,
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500',
            ],
            [
                'name' => 'Smartphone Pro Max',
                'sku' => 'TECH-SP-001',
                'price' => 999.99,
                'category' => 'electronics',
                'brand' => 'techpro',
                'description' => '6.7-inch OLED display, 5G enabled, triple camera system with 108MP main sensor, 256GB storage, and ultra-fast charging.',
                'short_description' => 'Latest flagship smartphone with amazing camera',
                'featured' => true,
                'quantity' => 25,
                'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500',
            ],
            [
                'name' => 'Wireless Gaming Mouse',
                'sku' => 'TECH-MS-001',
                'price' => 59.99,
                'category' => 'electronics',
                'brand' => 'techpro',
                'description' => 'Ergonomic gaming mouse with 16000 DPI, RGB lighting, and programmable buttons for competitive gaming.',
                'short_description' => 'Professional gaming mouse with RGB',
                'featured' => false,
                'quantity' => 100,
                'image' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500',
            ],
            [
                'name' => '4K Ultra HD Smart TV 55"',
                'sku' => 'TECH-TV-001',
                'price' => 649.99,
                'category' => 'electronics',
                'brand' => 'techpro',
                'description' => '55-inch 4K UHD Smart TV with HDR, built-in streaming apps, voice control, and stunning picture quality.',
                'short_description' => '55" 4K Smart TV with streaming apps',
                'featured' => true,
                'quantity' => 15,
                'image' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=500',
            ],
            [
                'name' => 'Mechanical Keyboard RGB',
                'sku' => 'TECH-KB-001',
                'price' => 89.99,
                'category' => 'electronics',
                'brand' => 'techpro',
                'description' => 'Mechanical gaming keyboard with Cherry MX switches, customizable RGB backlighting, and aluminum frame.',
                'short_description' => 'RGB mechanical keyboard for gaming',
                'featured' => false,
                'quantity' => 75,
                'image' => 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=500',
            ],

            // Clothing
            [
                'name' => 'Cotton T-Shirt - Premium',
                'sku' => 'CLOTH-TS-001',
                'price' => 24.99,
                'category' => 'clothing',
                'brand' => 'stylehub',
                'description' => '100% organic cotton t-shirt with comfortable fit, breathable fabric, and modern design. Available in multiple colors.',
                'short_description' => '100% cotton comfortable t-shirt',
                'featured' => true,
                'quantity' => 200,
                'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=500',
            ],
            [
                'name' => 'Denim Jeans - Slim Fit',
                'sku' => 'CLOTH-JN-001',
                'price' => 59.99,
                'category' => 'clothing',
                'brand' => 'stylehub',
                'description' => 'Classic slim-fit denim jeans made from premium denim fabric. Durable, stylish, and comfortable for everyday wear.',
                'short_description' => 'Premium slim-fit denim jeans',
                'featured' => true,
                'quantity' => 120,
                'image' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=500',
            ],
            [
                'name' => 'Leather Jacket',
                'sku' => 'CLOTH-LJ-001',
                'price' => 149.99,
                'category' => 'clothing',
                'brand' => 'stylehub',
                'description' => 'Genuine leather jacket with classic design, multiple pockets, and comfortable lining. Perfect for casual and semi-formal occasions.',
                'short_description' => 'Genuine leather jacket',
                'featured' => false,
                'quantity' => 40,
                'image' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=500',
            ],
            [
                'name' => 'Summer Dress - Floral',
                'sku' => 'CLOTH-DR-001',
                'price' => 45.99,
                'category' => 'clothing',
                'brand' => 'stylehub',
                'description' => 'Lightweight summer dress with beautiful floral pattern, perfect for warm weather and outdoor events.',
                'short_description' => 'Floral summer dress',
                'featured' => false,
                'quantity' => 80,
                'image' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=500',
            ],
            [
                'name' => 'Running Shoes - Sport',
                'sku' => 'CLOTH-SH-001',
                'price' => 79.99,
                'category' => 'clothing',
                'brand' => 'fitgear',
                'description' => 'Professional running shoes with cushioned sole, breathable mesh upper, and excellent grip for all terrains.',
                'short_description' => 'Comfortable running shoes',
                'featured' => true,
                'quantity' => 90,
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500',
            ],

            // Home & Living
            [
                'name' => 'Modern Table Lamp',
                'sku' => 'HOME-LAMP-001',
                'price' => 49.99,
                'category' => 'home-living',
                'brand' => 'homecomfort',
                'description' => 'Contemporary table lamp with adjustable brightness, elegant design, and energy-efficient LED bulbs.',
                'short_description' => 'Elegant table lamp for your workspace',
                'featured' => true,
                'quantity' => 65,
                'image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500',
            ],
            [
                'name' => 'Decorative Wall Art',
                'sku' => 'HOME-ART-001',
                'price' => 89.99,
                'category' => 'home-living',
                'brand' => 'homecomfort',
                'description' => 'Modern abstract wall art canvas print, ready to hang, adds sophistication to any room.',
                'short_description' => 'Abstract canvas wall art',
                'featured' => false,
                'quantity' => 45,
                'image' => 'https://images.unsplash.com/photo-1513519245088-0e12902e35ca?w=500',
            ],
            [
                'name' => 'Ceramic Dinner Set',
                'sku' => 'HOME-DIN-001',
                'price' => 129.99,
                'category' => 'home-living',
                'brand' => 'homecomfort',
                'description' => '16-piece ceramic dinner set with plates, bowls, and mugs. Dishwasher and microwave safe.',
                'short_description' => '16-piece ceramic dinner set',
                'featured' => false,
                'quantity' => 30,
                'image' => 'https://images.unsplash.com/photo-1578500494198-246f612d3b3d?w=500',
            ],
            [
                'name' => 'Plush Throw Blanket',
                'sku' => 'HOME-BLK-001',
                'price' => 39.99,
                'category' => 'home-living',
                'brand' => 'homecomfort',
                'description' => 'Ultra-soft plush throw blanket, perfect for cozy evenings. Machine washable and available in multiple colors.',
                'short_description' => 'Soft and cozy throw blanket',
                'featured' => false,
                'quantity' => 110,
                'image' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=500',
            ],
            [
                'name' => 'Indoor Plant Pot Set',
                'sku' => 'HOME-POT-001',
                'price' => 34.99,
                'category' => 'home-living',
                'brand' => 'homecomfort',
                'description' => 'Set of 3 ceramic plant pots with drainage holes and saucers. Perfect for succulents and small plants.',
                'short_description' => 'Ceramic plant pot set',
                'featured' => false,
                'quantity' => 95,
                'image' => 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=500',
            ],

            // Sports & Outdoors
            [
                'name' => 'Yoga Mat - Premium',
                'sku' => 'SPORT-YM-001',
                'price' => 29.99,
                'category' => 'sports-outdoors',
                'brand' => 'fitgear',
                'description' => 'Non-slip yoga mat with extra cushioning, eco-friendly material, and carrying strap included.',
                'short_description' => 'Premium non-slip yoga mat',
                'featured' => true,
                'quantity' => 85,
                'image' => 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=500',
            ],
            [
                'name' => 'Camping Tent 4-Person',
                'sku' => 'SPORT-TN-001',
                'price' => 159.99,
                'category' => 'sports-outdoors',
                'brand' => 'fitgear',
                'description' => 'Waterproof 4-person camping tent with easy setup, ventilation windows, and storage pockets.',
                'short_description' => 'Waterproof camping tent',
                'featured' => false,
                'quantity' => 20,
                'image' => 'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?w=500',
            ],
            [
                'name' => 'Mountain Bike 26"',
                'sku' => 'SPORT-BK-001',
                'price' => 399.99,
                'category' => 'sports-outdoors',
                'brand' => 'fitgear',
                'description' => '26-inch mountain bike with 21-speed gear system, dual disc brakes, and aluminum frame.',
                'short_description' => 'Professional mountain bike',
                'featured' => true,
                'quantity' => 12,
                'image' => 'https://images.unsplash.com/photo-1576435728678-68d0fbf94e91?w=500',
            ],

            // Books & Media
            [
                'name' => 'Bestseller Novel Collection',
                'sku' => 'BOOK-NV-001',
                'price' => 34.99,
                'category' => 'books-media',
                'brand' => 'readmore',
                'description' => 'Collection of 3 bestselling novels in hardcover. Perfect gift for book lovers.',
                'short_description' => 'Bestseller book collection',
                'featured' => false,
                'quantity' => 55,
                'image' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=500',
            ],
            [
                'name' => 'Educational Board Game',
                'sku' => 'BOOK-GM-001',
                'price' => 24.99,
                'category' => 'books-media',
                'brand' => 'readmore',
                'description' => 'Fun and educational board game for families, teaches strategy and critical thinking.',
                'short_description' => 'Family board game',
                'featured' => false,
                'quantity' => 70,
                'image' => 'https://images.unsplash.com/photo-1606167668584-78701c57f13d?w=500',
            ],
        ];

        foreach ($products as $product) {
            $categoryId = DB::table('categories')->where('slug', $product['category'])->value('id');
            $brandId = DB::table('brands')->where('slug', $product['brand'])->value('id');

            $productId = DB::table('products')->insertGetId([
                'name' => $product['name'],
                'sku' => $product['sku'],
                'slug' => Str::slug($product['name']),
                'type' => 'simple',
                'price' => $product['price'],
                'description' => $product['description'],
                'short_description' => $product['short_description'],
                'status' => 'enabled',
                'featured' => $product['featured'],
                'quantity' => $product['quantity'] ?? 100,
                'brand_id' => $brandId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Download and create product image if URL exists
            if ($productId && isset($product['image'])) {
                $filename = Str::slug($product['name']) . '-' . $productId . '.jpg';
                $localPath = $this->downloadImage($product['image'], $filename);
                
                if ($localPath) {
                    $imageId = DB::table('product_images')->insertGetId([
                        'product_id' => $productId,
                        'path' => $localPath,
                        'thumbnail_path' => $localPath,
                        'alt_text' => $product['name'],
                        'position' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    // Set as main image
                    if ($imageId) {
                        DB::table('products')->where('id', $productId)->update([
                            'main_image_id' => $imageId,
                        ]);
                    }
                }
            }
            
            // Attach category via pivot table
            if ($productId && $categoryId) {
                DB::table('category_product')->insertOrIgnore([
                    'category_id' => $categoryId,
                    'product_id' => $productId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedPages(): void
    {
        $pages = [
            [
                'title' => 'About Our Store',
                'url_key' => 'about-us',
                'content' => '<h1>About Us</h1><p>Welcome to our retail store! We offer a wide range of products for all your needs.</p>',
                'status' => 'published',
            ],
            [
                'title' => 'Contact Us',
                'url_key' => 'contact-us',
                'content' => '<h1>Contact Us</h1><p>Get in touch with our customer support team.</p>',
                'status' => 'published',
            ],
        ];

        foreach ($pages as $page) {
            DB::table('pages')->insertOrIgnore([
                'title' => $page['title'],
                'url_key' => $page['url_key'],
                'content' => $page['content'],
                'status' => $page['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedBlocks(): void
    {
        $blocks = [
            [
                'identifier' => 'retail-hero-banner',
                'title' => 'Welcome Banner',
                'content' => '<div class="hero-banner"><h2>Shop Latest Products</h2><p>Up to 50% Off on Selected Items</p></div>',
                'status' => 'active',
            ],
            [
                'identifier' => 'retail-featured-categories',
                'title' => 'Featured Categories',
                'content' => '<div class="featured-categories">Browse our popular categories</div>',
                'status' => 'active',
            ],
        ];

        foreach ($blocks as $block) {
            DB::table('blocks')->insertOrIgnore([
                'identifier' => $block['identifier'],
                'title' => $block['title'],
                'content' => $block['content'],
                'status' => $block['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
