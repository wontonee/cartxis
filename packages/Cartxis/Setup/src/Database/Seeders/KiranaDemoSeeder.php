<?php

declare(strict_types=1);

namespace Cartxis\Setup\Database\Seeders;

use Cartxis\Core\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KiranaDemoSeeder extends Seeder
{
    private ?Currency $defaultCurrency = null;

    /**
     * Run the database seeds for Kirana (Grocery) business type
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
                'name' => 'Fruits & Vegetables',
                'slug' => 'fruits-vegetables',
                'description' => 'Fresh fruits and vegetables daily',
                'status' => 'enabled',
            ],
            [
                'name' => 'Dairy Products',
                'slug' => 'dairy-products',
                'description' => 'Milk, cheese, yogurt and more',
                'status' => 'enabled',
            ],
            [
                'name' => 'Bakery & Snacks',
                'slug' => 'bakery-snacks',
                'description' => 'Bread, biscuits and snacks',
                'status' => 'enabled',
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages',
                'description' => 'Soft drinks, juices and water',
                'status' => 'enabled',
            ],
            [
                'name' => 'Household Essentials',
                'slug' => 'household-essentials',
                'description' => 'Cleaning and household products',
                'status' => 'enabled',
            ],
            [
                'name' => 'Personal Care',
                'slug' => 'personal-care',
                'description' => 'Soaps, shampoos and personal hygiene',
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
            ['name' => 'Fresh Farms', 'slug' => 'fresh-farms', 'is_featured' => true],
            ['name' => 'Mother Dairy', 'slug' => 'mother-dairy', 'is_featured' => true],
            ['name' => 'Britannia', 'slug' => 'britannia', 'is_featured' => true],
            ['name' => 'Coca-Cola', 'slug' => 'coca-cola', 'is_featured' => true],
            ['name' => 'Vim', 'slug' => 'vim', 'is_featured' => false],
            ['name' => 'Dove', 'slug' => 'dove', 'is_featured' => false],
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
            // Fruits & Vegetables
            [
                'name' => 'Fresh Bananas (1 Dozen)',
                'sku' => 'FV-BANANA-001',
                'price' => 2.99,
                'category' => 'fruits-vegetables',
                'brand' => 'fresh-farms',
                'description' => 'Farm-fresh ripe bananas, perfect for breakfast or snacks. Rich in potassium and natural energy.',
                'short_description' => 'Fresh ripe bananas from local farms',
                'featured' => true,
                'quantity' => 500,
                'image' => 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=500',
            ],
            [
                'name' => 'Fresh Tomatoes (1 Kg)',
                'sku' => 'FV-TOMATO-001',
                'price' => 1.49,
                'category' => 'fruits-vegetables',
                'brand' => 'fresh-farms',
                'description' => 'Red, juicy tomatoes perfect for cooking. Locally sourced and chemical-free.',
                'short_description' => 'Fresh red tomatoes',
                'featured' => false,
                'quantity' => 400,
                'image' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=500',
            ],
            [
                'name' => 'Organic Spinach (250g)',
                'sku' => 'FV-SPINACH-001',
                'price' => 1.99,
                'category' => 'fruits-vegetables',
                'brand' => 'fresh-farms',
                'description' => 'Fresh organic spinach leaves, washed and ready to cook. High in iron and vitamins.',
                'short_description' => 'Organic spinach leaves',
                'featured' => false,
                'quantity' => 200,
                'image' => 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=500',
            ],
            [
                'name' => 'Fresh Apples (1 Kg)',
                'sku' => 'FV-APPLE-001',
                'price' => 3.99,
                'category' => 'fruits-vegetables',
                'brand' => 'fresh-farms',
                'description' => 'Crisp and sweet apples, perfect for eating fresh or making desserts.',
                'short_description' => 'Fresh sweet apples',
                'featured' => true,
                'quantity' => 350,
                'image' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=500',
            ],

            // Dairy Products
            [
                'name' => 'Full Cream Milk (1 Liter)',
                'sku' => 'DAIRY-MILK-001',
                'price' => 1.99,
                'category' => 'dairy-products',
                'brand' => 'mother-dairy',
                'description' => 'Fresh full cream milk from healthy cows. Rich, creamy and nutritious.',
                'short_description' => 'Fresh full cream milk',
                'featured' => true,
                'quantity' => 300,
                'image' => 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=500',
            ],
            [
                'name' => 'Greek Yogurt (500g)',
                'sku' => 'DAIRY-YOGURT-001',
                'price' => 2.99,
                'category' => 'dairy-products',
                'brand' => 'mother-dairy',
                'description' => 'Thick and creamy Greek yogurt, high in protein and probiotics.',
                'short_description' => 'Creamy Greek yogurt',
                'featured' => false,
                'quantity' => 150,
                'image' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=500',
            ],
            [
                'name' => 'Paneer Cottage Cheese (200g)',
                'sku' => 'DAIRY-PANEER-001',
                'price' => 3.49,
                'category' => 'dairy-products',
                'brand' => 'mother-dairy',
                'description' => 'Fresh paneer made from pure milk. Great for curries and snacks.',
                'short_description' => 'Fresh cottage cheese',
                'featured' => true,
                'quantity' => 100,
                'image' => 'https://images.unsplash.com/photo-1628088062854-d1870b4553da?w=500',
            ],

            // Bakery & Snacks
            [
                'name' => 'Wheat Bread (Large)',
                'sku' => 'BAKERY-BREAD-001',
                'price' => 2.49,
                'category' => 'bakery-snacks',
                'brand' => 'britannia',
                'description' => 'Soft and fluffy whole wheat bread loaf, perfect for sandwiches and toast.',
                'short_description' => 'Whole wheat bread loaf',
                'featured' => true,
                'quantity' => 250,
                'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=500',
            ],
            [
                'name' => 'Chocolate Chip Cookies (300g)',
                'sku' => 'BAKERY-COOKIES-001',
                'price' => 3.99,
                'category' => 'bakery-snacks',
                'brand' => 'britannia',
                'description' => 'Delicious chocolate chip cookies, perfect for tea time snacking.',
                'short_description' => 'Chocolate chip cookies',
                'featured' => false,
                'quantity' => 180,
                'image' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=500',
            ],
            [
                'name' => 'Salted Crackers (500g)',
                'sku' => 'BAKERY-CRACKERS-001',
                'price' => 2.99,
                'category' => 'bakery-snacks',
                'brand' => 'britannia',
                'description' => 'Crispy salted crackers, great for snacking or serving with cheese.',
                'short_description' => 'Crispy salted crackers',
                'featured' => false,
                'quantity' => 220,
                'image' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=500',
            ],

            // Beverages
            [
                'name' => 'Coca-Cola (2 Liter)',
                'sku' => 'BEV-COKE-001',
                'price' => 3.49,
                'category' => 'beverages',
                'brand' => 'coca-cola',
                'description' => 'Refreshing cola drink, perfect for parties and gatherings.',
                'short_description' => 'Refreshing cola drink',
                'featured' => true,
                'quantity' => 400,
                'image' => 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=500',
            ],
            [
                'name' => 'Orange Juice (1 Liter)',
                'sku' => 'BEV-JUICE-001',
                'price' => 4.49,
                'category' => 'beverages',
                'brand' => 'coca-cola',
                'description' => '100% pure orange juice, no added sugar or preservatives.',
                'short_description' => 'Pure orange juice',
                'featured' => false,
                'quantity' => 160,
                'image' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=500',
            ],
            [
                'name' => 'Mineral Water (Pack of 6)',
                'sku' => 'BEV-WATER-001',
                'price' => 2.99,
                'category' => 'beverages',
                'brand' => 'coca-cola',
                'description' => 'Pure mineral water bottles, 1 liter each. Stay hydrated!',
                'short_description' => 'Pure mineral water',
                'featured' => false,
                'quantity' => 500,
                'image' => 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=500',
            ],

            // Household Essentials
            [
                'name' => 'Dishwash Liquid (500ml)',
                'sku' => 'HH-VIM-001',
                'price' => 2.99,
                'category' => 'household-essentials',
                'brand' => 'vim',
                'description' => 'Powerful dishwashing liquid that removes tough grease and stains.',
                'short_description' => 'Powerful dishwashing liquid',
                'featured' => false,
                'quantity' => 280,
                'image' => 'https://images.unsplash.com/photo-1563453392212-326f5e854473?w=500',
            ],
            [
                'name' => 'Laundry Detergent (1 Kg)',
                'sku' => 'HH-DETERGENT-001',
                'price' => 4.99,
                'category' => 'household-essentials',
                'brand' => 'vim',
                'description' => 'Effective laundry detergent powder for bright and clean clothes.',
                'short_description' => 'Laundry detergent powder',
                'featured' => true,
                'quantity' => 200,
                'image' => 'https://images.unsplash.com/photo-1610557892470-55d9e80c0bce?w=500',
            ],
            [
                'name' => 'Multi-Purpose Cleaner (750ml)',
                'sku' => 'HH-CLEANER-001',
                'price' => 3.49,
                'category' => 'household-essentials',
                'brand' => 'vim',
                'description' => 'All-purpose cleaner for floors, tiles, and surfaces.',
                'short_description' => 'Multi-purpose cleaner',
                'featured' => false,
                'quantity' => 150,
                'image' => 'https://images.unsplash.com/photo-1585421514738-01798e348b17?w=500',
            ],

            // Personal Care
            [
                'name' => 'Dove Soap Bar (Pack of 3)',
                'sku' => 'PC-DOVE-001',
                'price' => 4.99,
                'category' => 'personal-care',
                'brand' => 'dove',
                'description' => 'Moisturizing soap bars with 1/4 moisturizing cream for soft skin.',
                'short_description' => 'Moisturizing soap bars',
                'featured' => false,
                'quantity' => 300,
                'image' => 'https://images.unsplash.com/photo-1622909510916-66c6e4ff992a?w=500',
            ],
            [
                'name' => 'Shampoo - Smooth & Shine (400ml)',
                'sku' => 'PC-SHAMPOO-001',
                'price' => 5.99,
                'category' => 'personal-care',
                'brand' => 'dove',
                'description' => 'Nourishing shampoo for smooth, shiny, and manageable hair.',
                'short_description' => 'Smooth & shine shampoo',
                'featured' => true,
                'quantity' => 200,
                'image' => 'https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?w=500',
            ],
            [
                'name' => 'Toothpaste - Fresh Mint (150g)',
                'sku' => 'PC-TOOTHPASTE-001',
                'price' => 2.49,
                'category' => 'personal-care',
                'brand' => 'dove',
                'description' => 'Fresh mint toothpaste for strong teeth and fresh breath.',
                'short_description' => 'Fresh mint toothpaste',
                'featured' => false,
                'quantity' => 400,
                'image' => 'https://images.unsplash.com/photo-1528642474498-1af0c17fd8c3?w=500',
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
                'title' => 'About Our Kirana Store',
                'url_key' => 'about-us',
                'content' => '<h1>About Us</h1><p>Your neighborhood kirana store for all daily essentials!</p>',
                'status' => 'published',
            ],
            [
                'title' => 'Delivery Information',
                'url_key' => 'delivery-info',
                'content' => '<h1>Delivery</h1><p>We deliver fresh groceries to your doorstep within 2 hours!</p>',
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
                'identifier' => 'kirana-hero-banner',
                'title' => 'Daily Essentials Banner',
                'content' => '<div class="hero-banner"><h2>Fresh Groceries Daily</h2><p>Order now and get delivery within 2 hours</p></div>',
                'status' => 'active',
            ],
            [
                'identifier' => 'kirana-featured-products',
                'title' => 'Today\'s Fresh Arrivals',
                'content' => '<div class="featured-products">Check out fresh arrivals today!</div>',
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
