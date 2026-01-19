<?php

declare(strict_types=1);

namespace Cartxis\Setup\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ElectronicsDemoSeeder extends Seeder
{
    /**
     * Run the database seeds for Electronics business type
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
                'name' => 'Smartphones & Tablets',
                'slug' => 'smartphones-tablets',
                'description' => 'Latest smartphones and tablets from top brands',
                'status' => 'enabled',
            ],
            [
                'name' => 'Laptops & Computers',
                'slug' => 'laptops-computers',
                'description' => 'High-performance laptops and desktop computers',
                'status' => 'enabled',
            ],
            [
                'name' => 'Audio & Headphones',
                'slug' => 'audio-headphones',
                'description' => 'Premium headphones, earbuds, and speakers',
                'status' => 'enabled',
            ],
            [
                'name' => 'Cameras & Photography',
                'slug' => 'cameras-photography',
                'description' => 'Digital cameras and photography accessories',
                'status' => 'enabled',
            ],
            [
                'name' => 'Gaming',
                'slug' => 'gaming',
                'description' => 'Gaming consoles, accessories, and peripherals',
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
            ['name' => 'Apple', 'slug' => 'apple', 'is_featured' => true],
            ['name' => 'Samsung', 'slug' => 'samsung', 'is_featured' => true],
            ['name' => 'Sony', 'slug' => 'sony', 'is_featured' => true],
            ['name' => 'Dell', 'slug' => 'dell', 'is_featured' => true],
            ['name' => 'Canon', 'slug' => 'canon', 'is_featured' => false],
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
            // Smartphones & Tablets
            [
                'name' => 'iPhone 15 Pro Max',
                'sku' => 'PHONE-IP15-001',
                'price' => 1199.99,
                'category' => 'smartphones-tablets',
                'brand' => 'apple',
                'description' => 'Latest iPhone with A17 Pro chip, titanium design, and advanced camera system. 256GB storage, 6.7-inch Super Retina XDR display.',
                'short_description' => 'iPhone 15 Pro Max - 256GB',
                'featured' => true,
                'quantity' => 45,
                'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500',
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'sku' => 'PHONE-SS24-001',
                'price' => 1099.99,
                'category' => 'smartphones-tablets',
                'brand' => 'samsung',
                'description' => 'Flagship Samsung smartphone with S Pen, 200MP camera, and AI-powered features. 512GB storage.',
                'short_description' => 'Galaxy S24 Ultra - 512GB',
                'featured' => true,
                'quantity' => 50,
                'image' => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=500',
            ],
            [
                'name' => 'iPad Air 11"',
                'sku' => 'TAB-IPAD-001',
                'price' => 599.99,
                'category' => 'smartphones-tablets',
                'brand' => 'apple',
                'description' => 'Powerful iPad Air with M2 chip, stunning Liquid Retina display, and all-day battery life. Perfect for creativity and productivity.',
                'short_description' => 'iPad Air 11-inch',
                'featured' => false,
                'quantity' => 60,
                'image' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=500',
            ],

            // Laptops & Computers
            [
                'name' => 'MacBook Pro 16"',
                'sku' => 'LAP-MBP16-001',
                'price' => 2499.99,
                'category' => 'laptops-computers',
                'brand' => 'apple',
                'description' => 'Professional laptop with M3 Pro chip, 36GB RAM, and 1TB SSD. Stunning Liquid Retina XDR display for creative work.',
                'short_description' => 'MacBook Pro 16-inch M3',
                'featured' => true,
                'quantity' => 30,
                'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500',
            ],
            [
                'name' => 'Dell XPS 15',
                'sku' => 'LAP-DELL15-001',
                'price' => 1899.99,
                'category' => 'laptops-computers',
                'brand' => 'dell',
                'description' => 'Premium Windows laptop with Intel Core i9, 32GB RAM, 1TB SSD, and NVIDIA RTX 4060 graphics.',
                'short_description' => 'Dell XPS 15 - i9',
                'featured' => true,
                'quantity' => 40,
                'image' => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=500',
            ],
            [
                'name' => 'Gaming Desktop PC',
                'sku' => 'DESK-GAM-001',
                'price' => 2199.99,
                'category' => 'laptops-computers',
                'brand' => 'dell',
                'description' => 'High-performance gaming PC with AMD Ryzen 9, 64GB RAM, RTX 4080, and 2TB NVMe SSD. RGB lighting included.',
                'short_description' => 'Gaming PC - RTX 4080',
                'featured' => false,
                'quantity' => 20,
                'image' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?w=500',
            ],

            // Audio & Headphones
            [
                'name' => 'AirPods Pro (2nd Gen)',
                'sku' => 'AUDIO-APP-001',
                'price' => 249.99,
                'category' => 'audio-headphones',
                'brand' => 'apple',
                'description' => 'Premium wireless earbuds with active noise cancellation, transparency mode, and spatial audio. USB-C charging.',
                'short_description' => 'AirPods Pro 2nd Gen',
                'featured' => true,
                'quantity' => 150,
                'image' => 'https://images.unsplash.com/photo-1606841837239-c5a1a4a07af7?w=500',
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'sku' => 'AUDIO-SONY-001',
                'price' => 399.99,
                'category' => 'audio-headphones',
                'brand' => 'sony',
                'description' => 'Industry-leading noise canceling headphones with exceptional sound quality. 30-hour battery life.',
                'short_description' => 'Sony WH-1000XM5 Headphones',
                'featured' => true,
                'quantity' => 85,
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500',
            ],
            [
                'name' => 'Bluetooth Speaker - Portable',
                'sku' => 'AUDIO-SPK-001',
                'price' => 129.99,
                'category' => 'audio-headphones',
                'brand' => 'sony',
                'description' => 'Waterproof portable speaker with 360-degree sound. 12-hour battery and wireless connectivity.',
                'short_description' => 'Portable Bluetooth speaker',
                'featured' => false,
                'quantity' => 120,
                'image' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=500',
            ],
            [
                'name' => 'Studio Monitor Speakers',
                'sku' => 'AUDIO-MON-001',
                'price' => 349.99,
                'category' => 'audio-headphones',
                'brand' => 'sony',
                'description' => 'Professional studio monitors for music production. Accurate sound reproduction and clear audio.',
                'short_description' => 'Studio monitor speakers',
                'featured' => false,
                'quantity' => 55,
                'image' => 'https://images.unsplash.com/photo-1545454675-3531b543be5d?w=500',
            ],

            // Cameras & Photography
            [
                'name' => 'Canon EOS R6 Mark II',
                'sku' => 'CAM-CANR6-001',
                'price' => 2499.99,
                'category' => 'cameras-photography',
                'brand' => 'canon',
                'description' => 'Professional mirrorless camera with 24.2MP full-frame sensor, 4K 60fps video, and advanced autofocus.',
                'short_description' => 'Canon EOS R6 Mark II Body',
                'featured' => true,
                'quantity' => 25,
                'image' => 'https://images.unsplash.com/photo-1606941369441-589b9c6f0b72?w=500',
            ],
            [
                'name' => 'Sony A7 IV',
                'sku' => 'CAM-SONYA7-001',
                'price' => 2299.99,
                'category' => 'cameras-photography',
                'brand' => 'sony',
                'description' => 'Versatile full-frame mirrorless camera with 33MP sensor, 4K 60fps, and advanced image stabilization.',
                'short_description' => 'Sony A7 IV Camera Body',
                'featured' => true,
                'quantity' => 30,
                'image' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=500',
            ],
            [
                'name' => 'Camera Tripod - Carbon Fiber',
                'sku' => 'CAM-TRI-001',
                'price' => 199.99,
                'category' => 'cameras-photography',
                'brand' => 'canon',
                'description' => 'Professional carbon fiber tripod with ball head. Lightweight yet stable for any camera setup.',
                'short_description' => 'Carbon fiber tripod',
                'featured' => false,
                'quantity' => 75,
                'image' => 'https://images.unsplash.com/photo-1606244864456-8bee63fce472?w=500',
            ],

            // Gaming
            [
                'name' => 'PlayStation 5 Console',
                'sku' => 'GAM-PS5-001',
                'price' => 499.99,
                'category' => 'gaming',
                'brand' => 'sony',
                'description' => 'Next-gen gaming console with ultra-high-speed SSD, 4K gaming, and ray tracing. Includes DualSense controller.',
                'short_description' => 'PS5 Console',
                'featured' => true,
                'quantity' => 35,
                'image' => 'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=500',
            ],
            [
                'name' => 'Gaming Headset RGB',
                'sku' => 'GAM-HEAD-001',
                'price' => 89.99,
                'category' => 'gaming',
                'brand' => 'sony',
                'description' => 'Professional gaming headset with 7.1 surround sound, RGB lighting, and noise-canceling microphone.',
                'short_description' => 'RGB gaming headset',
                'featured' => false,
                'quantity' => 110,
                'image' => 'https://images.unsplash.com/photo-1599669454699-248893623440?w=500',
            ],
            [
                'name' => 'Mechanical Gaming Keyboard',
                'sku' => 'GAM-KEY-001',
                'price' => 149.99,
                'category' => 'gaming',
                'brand' => 'dell',
                'description' => 'RGB mechanical keyboard with Cherry MX switches, programmable keys, and aluminum build.',
                'short_description' => 'Mechanical gaming keyboard',
                'featured' => false,
                'quantity' => 95,
                'image' => 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=500',
            ],
            [
                'name' => 'Gaming Mouse - Wireless',
                'sku' => 'GAM-MOU-001',
                'price' => 79.99,
                'category' => 'gaming',
                'brand' => 'dell',
                'description' => 'High-precision wireless gaming mouse with 20000 DPI, RGB lighting, and programmable buttons.',
                'short_description' => 'Wireless gaming mouse',
                'featured' => false,
                'quantity' => 130,
                'image' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500',
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
                'title' => 'About Our Electronics Store',
                'url_key' => 'about-us',
                'content' => '<h1>About Us</h1><p>Your trusted source for the latest technology and electronics!</p>',
                'status' => 'published',
            ],
            [
                'title' => 'Warranty Information',
                'url_key' => 'warranty',
                'content' => '<h1>Warranty</h1><p>Learn about our comprehensive warranty coverage and support.</p>',
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
                'identifier' => 'electronics-hero-banner',
                'title' => 'Electronics Hero Banner',
                'content' => '<div class="hero">Latest Tech Deals - Save up to 40%</div>',
                'status' => 'active',
            ],
            [
                'identifier' => 'electronics-footer-info',
                'title' => 'Electronics Footer Info',
                'content' => '<p>Your technology partner since 2020.</p>',
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
