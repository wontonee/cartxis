<?php

declare(strict_types=1);

namespace Cartxis\Setup\Database\Seeders;

use Cartxis\Core\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FashionDemoSeeder extends Seeder
{
    private ?Currency $defaultCurrency = null;

    /**
     * Run the database seeds for Fashion business type
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
                'name' => 'Men\'s Fashion',
                'slug' => 'mens-fashion',
                'description' => 'Latest trends in men\'s clothing and accessories',
                'status' => 'enabled',
            ],
            [
                'name' => 'Women\'s Fashion',
                'slug' => 'womens-fashion',
                'description' => 'Elegant and stylish women\'s wear collection',
                'status' => 'enabled',
            ],
            [
                'name' => 'Kids Fashion',
                'slug' => 'kids-fashion',
                'description' => 'Comfortable and trendy clothing for kids',
                'status' => 'enabled',
            ],
            [
                'name' => 'Footwear',
                'slug' => 'footwear',
                'description' => 'Shoes and sandals for all occasions',
                'status' => 'enabled',
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Fashion accessories and jewelry',
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
            ['name' => 'Zara', 'slug' => 'zara', 'is_featured' => true],
            ['name' => 'H&M', 'slug' => 'hm', 'is_featured' => true],
            ['name' => 'Nike', 'slug' => 'nike', 'is_featured' => true],
            ['name' => 'Adidas', 'slug' => 'adidas', 'is_featured' => true],
            ['name' => 'Levi\'s', 'slug' => 'levis', 'is_featured' => false],
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
            // Men's Fashion
            [
                'name' => 'Classic Denim Jacket',
                'sku' => 'MEN-JK-001',
                'price' => 89.99,
                'category' => 'mens-fashion',
                'brand' => 'levis',
                'description' => 'Timeless denim jacket with a modern fit. Made from premium quality denim with classic button closure and multiple pockets.',
                'short_description' => 'Classic denim jacket for men',
                'featured' => true,
                'quantity' => 75,
                'image' => 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=500',
            ],
            [
                'name' => 'Slim Fit Chinos',
                'sku' => 'MEN-PN-001',
                'price' => 49.99,
                'category' => 'mens-fashion',
                'brand' => 'zara',
                'description' => 'Comfortable slim fit chino pants perfect for casual and semi-formal occasions. Available in multiple colors.',
                'short_description' => 'Slim fit casual chinos',
                'featured' => false,
                'quantity' => 120,
                'image' => 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=500',
            ],
            [
                'name' => 'Cotton Polo Shirt',
                'sku' => 'MEN-TS-001',
                'price' => 34.99,
                'category' => 'mens-fashion',
                'brand' => 'hm',
                'description' => '100% cotton polo shirt with classic collar design. Breathable fabric perfect for everyday wear.',
                'short_description' => 'Classic cotton polo',
                'featured' => false,
                'quantity' => 200,
                'image' => 'https://images.unsplash.com/photo-1586363104862-3a5e2ab60d99?w=500',
            ],
            [
                'name' => 'Leather Belt - Brown',
                'sku' => 'MEN-AC-001',
                'price' => 29.99,
                'category' => 'mens-fashion',
                'brand' => 'levis',
                'description' => 'Genuine leather belt with classic buckle. Durable and stylish accessory for any outfit.',
                'short_description' => 'Premium leather belt',
                'featured' => false,
                'quantity' => 150,
                'image' => 'https://images.unsplash.com/photo-1624222247344-550fb60583c2?w=500',
            ],

            // Women's Fashion
            [
                'name' => 'Floral Maxi Dress',
                'sku' => 'WOM-DR-001',
                'price' => 79.99,
                'category' => 'womens-fashion',
                'brand' => 'zara',
                'description' => 'Elegant floral print maxi dress perfect for summer occasions. Flowing silhouette with adjustable straps.',
                'short_description' => 'Beautiful floral maxi dress',
                'featured' => true,
                'quantity' => 85,
                'image' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=500',
            ],
            [
                'name' => 'High-Waist Skinny Jeans',
                'sku' => 'WOM-JN-001',
                'price' => 64.99,
                'category' => 'womens-fashion',
                'brand' => 'levis',
                'description' => 'Flattering high-waist skinny jeans with stretch denim for comfort and style all day long.',
                'short_description' => 'High-waist skinny jeans',
                'featured' => true,
                'quantity' => 110,
                'image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=500',
            ],
            [
                'name' => 'Silk Blouse',
                'sku' => 'WOM-BL-001',
                'price' => 54.99,
                'category' => 'womens-fashion',
                'brand' => 'hm',
                'description' => 'Luxurious silk blouse with elegant draping. Perfect for office wear or evening events.',
                'short_description' => 'Elegant silk blouse',
                'featured' => false,
                'quantity' => 90,
                'image' => 'https://images.unsplash.com/photo-1582142306909-195724d06341?w=500',
            ],
            [
                'name' => 'Leather Handbag',
                'sku' => 'WOM-HB-001',
                'price' => 129.99,
                'category' => 'womens-fashion',
                'brand' => 'zara',
                'description' => 'Premium leather handbag with multiple compartments. Spacious and stylish for everyday use.',
                'short_description' => 'Designer leather handbag',
                'featured' => true,
                'quantity' => 60,
                'image' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=500',
            ],
            [
                'name' => 'Knit Cardigan',
                'sku' => 'WOM-CR-001',
                'price' => 44.99,
                'category' => 'womens-fashion',
                'brand' => 'hm',
                'description' => 'Cozy knit cardigan perfect for layering. Soft fabric with button closure.',
                'short_description' => 'Comfortable knit cardigan',
                'featured' => false,
                'quantity' => 130,
                'image' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=500',
            ],

            // Kids Fashion
            [
                'name' => 'Kids Cotton T-Shirt Set',
                'sku' => 'KID-TS-001',
                'price' => 24.99,
                'category' => 'kids-fashion',
                'brand' => 'hm',
                'description' => 'Pack of 3 colorful cotton t-shirts for kids. Soft, breathable, and durable for active play.',
                'short_description' => '3-pack kids t-shirts',
                'featured' => false,
                'quantity' => 180,
                'image' => 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?w=500',
            ],
            [
                'name' => 'Kids Denim Shorts',
                'sku' => 'KID-SH-001',
                'price' => 29.99,
                'category' => 'kids-fashion',
                'brand' => 'levis',
                'description' => 'Comfortable denim shorts perfect for summer. Adjustable waist and durable construction.',
                'short_description' => 'Kids denim shorts',
                'featured' => false,
                'quantity' => 140,
                'image' => 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?w=500',
            ],
            [
                'name' => 'Girls Party Dress',
                'sku' => 'KID-DR-001',
                'price' => 39.99,
                'category' => 'kids-fashion',
                'brand' => 'zara',
                'description' => 'Adorable party dress with bow detail. Perfect for special occasions and celebrations.',
                'short_description' => 'Girls party dress',
                'featured' => true,
                'quantity' => 75,
                'image' => 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?w=500',
            ],

            // Footwear
            [
                'name' => 'Running Shoes - Men',
                'sku' => 'FOOT-RS-001',
                'price' => 89.99,
                'category' => 'footwear',
                'brand' => 'nike',
                'description' => 'Lightweight running shoes with responsive cushioning. Perfect for daily runs and workouts.',
                'short_description' => 'Nike running shoes',
                'featured' => true,
                'quantity' => 95,
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500',
            ],
            [
                'name' => 'Casual Sneakers',
                'sku' => 'FOOT-SN-001',
                'price' => 69.99,
                'category' => 'footwear',
                'brand' => 'adidas',
                'description' => 'Classic casual sneakers for everyday wear. Comfortable and versatile design.',
                'short_description' => 'Adidas casual sneakers',
                'featured' => true,
                'quantity' => 120,
                'image' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=500',
            ],
            [
                'name' => 'Women\'s Ankle Boots',
                'sku' => 'FOOT-BT-001',
                'price' => 99.99,
                'category' => 'footwear',
                'brand' => 'zara',
                'description' => 'Stylish ankle boots with block heel. Perfect for autumn and winter outfits.',
                'short_description' => 'Elegant ankle boots',
                'featured' => false,
                'quantity' => 70,
                'image' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=500',
            ],
            [
                'name' => 'Kids Sport Shoes',
                'sku' => 'FOOT-KS-001',
                'price' => 44.99,
                'category' => 'footwear',
                'brand' => 'nike',
                'description' => 'Durable sport shoes designed for active kids. Supportive and comfortable.',
                'short_description' => 'Kids sports shoes',
                'featured' => false,
                'quantity' => 110,
                'image' => 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=500',
            ],

            // Accessories
            [
                'name' => 'Sunglasses - Classic',
                'sku' => 'ACC-SG-001',
                'price' => 39.99,
                'category' => 'accessories',
                'brand' => 'zara',
                'description' => 'Classic sunglasses with UV protection. Stylish design suitable for any face shape.',
                'short_description' => 'UV protection sunglasses',
                'featured' => false,
                'quantity' => 160,
                'image' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=500',
            ],
            [
                'name' => 'Leather Wallet',
                'sku' => 'ACC-WL-001',
                'price' => 34.99,
                'category' => 'accessories',
                'brand' => 'levis',
                'description' => 'Genuine leather wallet with multiple card slots and bill compartments.',
                'short_description' => 'Premium leather wallet',
                'featured' => false,
                'quantity' => 200,
                'image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=500',
            ],
            [
                'name' => 'Fashion Watch',
                'sku' => 'ACC-WT-001',
                'price' => 79.99,
                'category' => 'accessories',
                'brand' => 'zara',
                'description' => 'Elegant fashion watch with metal strap. Water-resistant and stylish.',
                'short_description' => 'Elegant fashion watch',
                'featured' => true,
                'quantity' => 85,
                'image' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?w=500',
            ],
        ];

        foreach ($products as $product) {
            $categoryId = DB::table('categories')->where('slug', $product['category'])->value('id');
            $brandId = DB::table('brands')->where('slug', $product['brand'])->value('id');

            $slug = Str::slug($product['name']);
            $existingProduct = DB::table('products')
                ->where('slug', $slug)
                ->first(['id', 'main_image_id']);

            if ($existingProduct) {
                DB::table('products')->where('id', $existingProduct->id)->update([
                    'name' => $product['name'],
                    'sku' => $product['sku'],
                    'type' => 'simple',
                    'price' => $this->toStorePrice($product['price']),
                    'description' => $product['description'],
                    'short_description' => $product['short_description'],
                    'status' => 'enabled',
                    'featured' => $product['featured'],
                    'quantity' => $product['quantity'] ?? 100,
                    'brand_id' => $brandId,
                    'updated_at' => now(),
                ]);

                $productId = $existingProduct->id;
            } else {
                $productId = DB::table('products')->insertGetId([
                    'name' => $product['name'],
                    'sku' => $product['sku'],
                    'slug' => $slug,
                    'type' => 'simple',
                    'price' => $this->toStorePrice($product['price']),
                    'description' => $product['description'],
                    'short_description' => $product['short_description'],
                    'status' => 'enabled',
                    'featured' => $product['featured'],
                    'quantity' => $product['quantity'] ?? 100,
                    'brand_id' => $brandId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Download and create product image if URL exists
            if ($productId && isset($product['image'])) {
                $hasMainImage = $existingProduct?->main_image_id || DB::table('products')->where('id', $productId)->whereNotNull('main_image_id')->exists();
                if ($hasMainImage) {
                    // Skip downloading/creating images if one already exists for this product
                    continue;
                }

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

    private function toStorePrice(float $basePrice): float
    {
        $currency = $this->getDefaultCurrency();
        if (! $currency) {
            return round($basePrice, 2);
        }

        $exchangeRate = (float) $currency->exchange_rate;
        $decimalPlaces = max(0, (int) $currency->decimal_places);

        if ($exchangeRate <= 0) {
            return round($basePrice, $decimalPlaces);
        }

        return round($basePrice * $exchangeRate, $decimalPlaces);
    }

    private function getDefaultCurrency(): ?Currency
    {
        if ($this->defaultCurrency instanceof Currency) {
            return $this->defaultCurrency;
        }

        $this->defaultCurrency = Currency::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        if (! $this->defaultCurrency) {
            $this->defaultCurrency = Currency::query()
                ->where('is_default', true)
                ->first();
        }

        return $this->defaultCurrency;
    }

    private function seedPages(): void
    {
        $pages = [
            [
                'title' => 'About Our Fashion Store',
                'url_key' => 'about-us',
                'content' => '<h1>About Us</h1><p>Your destination for the latest fashion trends!</p>',
                'status' => 'published',
            ],
            [
                'title' => 'Size Guide',
                'url_key' => 'size-guide',
                'content' => '<h1>Size Guide</h1><p>Find your perfect fit with our comprehensive sizing chart.</p>',
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
                'identifier' => 'fashion-hero-banner',
                'title' => 'Fashion Hero Banner',
                'content' => '<div class="hero">New Season Collection - Up to 50% Off</div>',
                'status' => 'active',
            ],
            [
                'identifier' => 'fashion-footer-info',
                'title' => 'Fashion Footer Info',
                'content' => '<p>Style meets comfort at our fashion store.</p>',
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
