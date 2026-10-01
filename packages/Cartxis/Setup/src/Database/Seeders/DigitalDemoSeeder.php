<?php

declare(strict_types=1);

namespace Cartxis\Setup\Database\Seeders;

use Cartxis\Core\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DigitalDemoSeeder extends Seeder
{
    private ?Currency $defaultCurrency = null;

    public function run(): void
    {
        $this->seedCategories();
        $this->seedProducts();
    }

    private function seedCategories(): void
    {
        $categories = [
            [
                'name' => 'Ebooks',
                'slug' => 'ebooks',
                'description' => 'Downloadable books and guides',
                'status' => 'enabled',
            ],
            [
                'name' => 'Software',
                'slug' => 'software',
                'description' => 'Apps, plugins, and digital tools',
                'status' => 'enabled',
            ],
            [
                'name' => 'Courses',
                'slug' => 'courses',
                'description' => 'Online learning materials',
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

    private function seedProducts(): void
    {
        $products = [
            [
                'name' => 'Starter Ecommerce Playbook (PDF)',
                'sku' => 'DIG-EBOOK-001',
                'price' => 19.99,
                'category' => 'ebooks',
                'type' => 'downloadable',
                'description' => 'A practical PDF guide covering catalog setup, checkout, and launch checklists for new stores.',
                'short_description' => 'PDF guide for launching an online store',
                'featured' => true,
            ],
            [
                'name' => 'Theme Starter Kit',
                'sku' => 'DIG-SOFT-001',
                'price' => 49.00,
                'category' => 'software',
                'type' => 'downloadable',
                'description' => 'Sample digital package demonstrating downloadable product delivery after purchase.',
                'short_description' => 'Downloadable software starter package',
                'featured' => true,
            ],
            [
                'name' => 'Product Photography Mini-Course',
                'sku' => 'DIG-COURSE-001',
                'price' => 29.00,
                'category' => 'courses',
                'type' => 'virtual',
                'description' => 'Virtual product example for online courses and digital services with no physical shipping.',
                'short_description' => 'Virtual course — no shipping required',
                'featured' => false,
            ],
        ];

        foreach ($products as $product) {
            $categoryId = DB::table('categories')->where('slug', $product['category'])->value('id');

            $exists = DB::table('products')->where('sku', $product['sku'])->exists();
            if ($exists) {
                continue;
            }

            $productId = DB::table('products')->insertGetId([
                'name' => $product['name'],
                'sku' => $product['sku'],
                'slug' => Str::slug($product['name']),
                'type' => $product['type'],
                'price' => $this->toStorePrice($product['price']),
                'description' => $product['description'],
                'short_description' => $product['short_description'],
                'status' => 'enabled',
                'featured' => $product['featured'],
                'quantity' => 9999,
                'manage_stock' => false,
                'stock_status' => 'in_stock',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

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
        if ($this->defaultCurrency !== null) {
            return $this->defaultCurrency;
        }

        $this->defaultCurrency = Currency::query()->where('is_default', true)->first()
            ?? Currency::query()->where('is_active', true)->orderBy('sort_order')->first();

        return $this->defaultCurrency;
    }
}
