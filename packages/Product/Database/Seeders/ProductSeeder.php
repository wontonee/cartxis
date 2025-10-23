<?php

namespace Vortex\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Vortex\Product\Models\Product;
use Vortex\Product\Models\Category;
use Vortex\Product\Models\Brand;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some categories
        $categories = Category::take(5)->get();

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please seed categories first.');
            return;
        }

        // Get brands for assignment
        $brands = Brand::all();
        $brandMap = [
            'sony' => $brands->firstWhere('slug', 'sony')?->id,
            'nike' => $brands->firstWhere('slug', 'nike')?->id,
            'adidas' => $brands->firstWhere('slug', 'adidas')?->id,
            'hp' => $brands->firstWhere('slug', 'hp')?->id,
            'logitech' => $brands->firstWhere('slug', 'logitech')?->id,
            'apple' => $brands->firstWhere('slug', 'apple')?->id,
            'samsung' => $brands->firstWhere('slug', 'samsung')?->id,
            'microsoft' => $brands->firstWhere('slug', 'microsoft')?->id,
            'puma' => $brands->firstWhere('slug', 'puma')?->id,
            'bose' => $brands->firstWhere('slug', 'bose')?->id,
            'jbl' => $brands->firstWhere('slug', 'jbl')?->id,
            'canon' => $brands->firstWhere('slug', 'canon')?->id,
        ];

        $products = [
            [
                'name' => 'Classic White T-Shirt',
                'short_description' => 'Comfortable cotton t-shirt for everyday wear',
                'description' => '<p>High-quality cotton t-shirt perfect for casual wear. Features a classic crew neck design and comfortable fit.</p>',
                'price' => 29.99,
                'quantity' => 150,
                'featured' => true,
                'new' => true,
                'brand_slug' => 'nike',
            ],
            [
                'name' => 'Denim Jeans',
                'short_description' => 'Premium denim jeans with modern fit',
                'description' => '<p>Classic denim jeans with a modern slim fit. Made from premium denim fabric that gets better with age.</p>',
                'price' => 79.99,
                'special_price' => 59.99,
                'quantity' => 75,
                'featured' => true,
                'brand_slug' => 'adidas',
            ],
            [
                'name' => 'Running Shoes',
                'short_description' => 'Lightweight running shoes for athletes',
                'description' => '<p>Professional-grade running shoes designed for comfort and performance. Featuring cushioned sole and breathable mesh upper.</p>',
                'price' => 120.00,
                'quantity' => 45,
                'new' => true,
                'brand_slug' => 'nike',
            ],
            [
                'name' => 'Leather Wallet',
                'short_description' => 'Genuine leather wallet with RFID protection',
                'description' => '<p>Premium leather wallet with RFID blocking technology. Multiple card slots and bill compartment.</p>',
                'price' => 49.99,
                'quantity' => 200,
                'brand_slug' => 'samsung',
            ],
            [
                'name' => 'Wireless Headphones',
                'short_description' => 'Bluetooth wireless headphones with noise cancellation',
                'description' => '<p>High-quality wireless headphones with active noise cancellation. Up to 30 hours of battery life.</p>',
                'price' => 199.99,
                'special_price' => 149.99,
                'quantity' => 30,
                'featured' => true,
                'new' => true,
                'brand_slug' => 'sony',
            ],
            [
                'name' => 'Cotton Hoodie',
                'short_description' => 'Comfortable cotton hoodie for casual wear',
                'description' => '<p>Soft cotton hoodie perfect for layering. Features kangaroo pocket and adjustable drawstring hood.</p>',
                'price' => 59.99,
                'quantity' => 100,
                'brand_slug' => 'puma',
            ],
            [
                'name' => 'Laptop Backpack',
                'short_description' => 'Durable backpack for laptops up to 15 inches',
                'description' => '<p>Professional laptop backpack with padded compartments. Water-resistant material and multiple pockets.</p>',
                'price' => 89.99,
                'quantity' => 60,
                'brand_slug' => 'hp',
            ],
            [
                'name' => 'Stainless Steel Watch',
                'short_description' => 'Classic stainless steel wristwatch',
                'description' => '<p>Elegant stainless steel watch with quartz movement. Water-resistant up to 50 meters.</p>',
                'price' => 249.99,
                'quantity' => 25,
                'featured' => true,
                'brand_slug' => 'apple',
            ],
            [
                'name' => 'Yoga Mat',
                'short_description' => 'Non-slip yoga mat for all levels',
                'description' => '<p>High-density foam yoga mat with excellent grip. Includes carrying strap for easy transport.</p>',
                'price' => 39.99,
                'quantity' => 80,
                'brand_slug' => 'nike',
            ],
            [
                'name' => 'Coffee Maker',
                'short_description' => 'Programmable coffee maker with timer',
                'description' => '<p>12-cup programmable coffee maker with auto-brew timer. Keep-warm function and removable filter basket.</p>',
                'price' => 79.99,
                'quantity' => 40,
                'brand_slug' => 'samsung',
            ],
            [
                'name' => 'Wireless Mouse',
                'short_description' => 'Ergonomic wireless mouse',
                'description' => '<p>Comfortable wireless mouse with ergonomic design. Long battery life and precise tracking.</p>',
                'price' => 29.99,
                'quantity' => 150,
                'brand_slug' => 'logitech',
            ],
            [
                'name' => 'Desk Lamp',
                'short_description' => 'LED desk lamp with adjustable brightness',
                'description' => '<p>Modern LED desk lamp with multiple brightness levels. Flexible arm for perfect positioning.</p>',
                'price' => 49.99,
                'quantity' => 70,
                'brand_slug' => 'samsung',
            ],
            [
                'name' => 'Phone Case',
                'short_description' => 'Protective phone case with card holder',
                'description' => '<p>Durable phone case with built-in card holder. Drop protection and precise cutouts.</p>',
                'price' => 24.99,
                'quantity' => 200,
                'brand_slug' => 'apple',
            ],
            [
                'name' => 'Travel Mug',
                'short_description' => 'Insulated travel mug keeps drinks hot/cold',
                'description' => '<p>Stainless steel travel mug with vacuum insulation. Leak-proof lid and fits most cup holders.</p>',
                'price' => 34.99,
                'quantity' => 120,
                'brand_slug' => 'samsung',
            ],
            [
                'name' => 'Bluetooth Speaker',
                'short_description' => 'Portable Bluetooth speaker with bass',
                'description' => '<p>Compact Bluetooth speaker with powerful sound. Water-resistant and 12-hour battery life.</p>',
                'price' => 69.99,
                'special_price' => 54.99,
                'quantity' => 55,
                'brand_slug' => 'jbl',
            ],
            [
                'name' => 'Reading Glasses',
                'short_description' => 'Blue light blocking reading glasses',
                'description' => '<p>Stylish reading glasses with blue light blocking technology. Reduces eye strain from screens.</p>',
                'price' => 39.99,
                'quantity' => 90,
                'brand_slug' => 'sony',
            ],
            [
                'name' => 'Kitchen Knife Set',
                'short_description' => 'Professional chef knife set',
                'description' => '<p>8-piece professional knife set with wooden block. High-carbon stainless steel blades.</p>',
                'price' => 159.99,
                'quantity' => 35,
                'brand_slug' => 'samsung',
            ],
            [
                'name' => 'Fitness Tracker',
                'short_description' => 'Smart fitness tracker with heart rate monitor',
                'description' => '<p>Advanced fitness tracker with heart rate monitoring, sleep tracking, and waterproof design.</p>',
                'price' => 99.99,
                'quantity' => 65,
                'new' => true,
                'brand_slug' => 'apple',
            ],
            [
                'name' => 'Bedding Set',
                'short_description' => 'Queen size microfiber bedding set',
                'description' => '<p>Soft microfiber bedding set including duvet cover, pillowcases, and fitted sheet.</p>',
                'price' => 89.99,
                'quantity' => 50,
                'brand_slug' => 'samsung',
            ],
            [
                'name' => 'Tool Set',
                'short_description' => 'Complete home tool kit',
                'description' => '<p>100-piece complete tool set with storage case. Everything you need for home repairs.</p>',
                'price' => 129.99,
                'quantity' => 40,
                'brand_slug' => 'hp',
            ],
        ];

        foreach ($products as $productData) {
            // Generate slug
            $slug = Str::slug($productData['name']);

            // Check if product already exists
            $product = Product::where('slug', $slug)->first();

            // Get brand_id from the brand_slug if provided
            $brandId = null;
            if (isset($productData['brand_slug']) && isset($brandMap[$productData['brand_slug']])) {
                $brandId = $brandMap[$productData['brand_slug']];
            }

            if ($product) {
                // Update existing product with brand
                $product->update([
                    'brand_id' => $brandId,
                    'price' => $productData['price'],
                    'special_price' => $productData['special_price'] ?? null,
                    'quantity' => $productData['quantity'],
                    'stock_status' => $productData['quantity'] > 0 ? 'in_stock' : 'out_of_stock',
                    'featured' => $productData['featured'] ?? false,
                    'new' => $productData['new'] ?? false,
                ]);
                $this->command->info("Updated product: {$product->name} (Brand: " . ($brandId ? $productData['brand_slug'] : 'None') . ")");
            } else {
                // Generate SKU from name
                $sku = 'PRD-' . strtoupper(substr(str_replace([' ', '-'], '', $productData['name']), 0, 6)) . '-' . rand(1000, 9999);

                $product = Product::create([
                    'sku' => $sku,
                    'slug' => $slug,
                    'name' => $productData['name'],
                    'short_description' => $productData['short_description'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'special_price' => $productData['special_price'] ?? null,
                    'quantity' => $productData['quantity'],
                    'brand_id' => $brandId,
                    'status' => 'enabled',
                    'visibility' => 'both',
                    'type' => 'simple',
                    'stock_status' => $productData['quantity'] > 0 ? 'in_stock' : 'out_of_stock',
                    'featured' => $productData['featured'] ?? false,
                    'new' => $productData['new'] ?? false,
                    'manage_stock' => true,
                    'min_quantity' => 1,
                    'max_quantity' => 10,
                    'weight' => rand(100, 5000) . 'g',
                    'meta_title' => $productData['name'] . ' - Vortex Commerce',
                    'meta_description' => $productData['short_description'],
                ]);

                // Attach random categories (1-3 categories per product)
                $randomCategories = $categories->random(rand(1, min(3, $categories->count())));
                $product->categories()->attach($randomCategories->pluck('id'));

                $this->command->info("Created product: {$product->name} (Brand: " . ($brandId ? $productData['brand_slug'] : 'None') . ")");
            }
        }

        $this->command->info('Product seeding completed!');
    }
}
