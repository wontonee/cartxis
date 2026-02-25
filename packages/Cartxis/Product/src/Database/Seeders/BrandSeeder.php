<?php

namespace Cartxis\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\Product\Models\Brand;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Apple',
                'slug' => 'apple',
                'description' => 'Premium technology and consumer electronics',
                'logo' => null,
                'website_url' => 'https://www.apple.com',
                'is_featured' => true,
                'status' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Samsung',
                'slug' => 'samsung',
                'description' => 'Leading electronics and technology brand',
                'logo' => null,
                'website_url' => 'https://www.samsung.com',
                'is_featured' => true,
                'status' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Sony',
                'slug' => 'sony',
                'description' => 'Electronics, gaming, and entertainment',
                'logo' => null,
                'website_url' => 'https://www.sony.com',
                'is_featured' => true,
                'status' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => 'Nike',
                'slug' => 'nike',
                'description' => 'Athletic footwear and apparel',
                'logo' => null,
                'website_url' => 'https://www.nike.com',
                'is_featured' => true,
                'status' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => 'Adidas',
                'slug' => 'adidas',
                'description' => 'Sports and lifestyle brand',
                'logo' => null,
                'website_url' => 'https://www.adidas.com',
                'is_featured' => true,
                'status' => 1,
                'sort_order' => 5,
            ],
            [
                'name' => 'Puma',
                'slug' => 'puma',
                'description' => 'Sports shoes and lifestyle products',
                'logo' => null,
                'website_url' => 'https://www.puma.com',
                'is_featured' => false,
                'status' => 1,
                'sort_order' => 6,
            ],
            [
                'name' => 'HP',
                'slug' => 'hp',
                'description' => 'Computing and printing solutions',
                'logo' => null,
                'website_url' => 'https://www.hp.com',
                'is_featured' => false,
                'status' => 1,
                'sort_order' => 7,
            ],
            [
                'name' => 'Dell',
                'slug' => 'dell',
                'description' => 'Computer technology and solutions',
                'logo' => null,
                'website_url' => 'https://www.dell.com',
                'is_featured' => false,
                'status' => 1,
                'sort_order' => 8,
            ],
            [
                'name' => 'Lenovo',
                'slug' => 'lenovo',
                'description' => 'Personal computers and electronics',
                'logo' => null,
                'website_url' => 'https://www.lenovo.com',
                'is_featured' => false,
                'status' => 1,
                'sort_order' => 9,
            ],
            [
                'name' => 'Logitech',
                'slug' => 'logitech',
                'description' => 'Computer peripherals and accessories',
                'logo' => null,
                'website_url' => 'https://www.logitech.com',
                'is_featured' => false,
                'status' => 1,
                'sort_order' => 10,
            ],
            [
                'name' => 'Canon',
                'slug' => 'canon',
                'description' => 'Cameras and imaging products',
                'logo' => null,
                'website_url' => 'https://www.canon.com',
                'is_featured' => false,
                'status' => 1,
                'sort_order' => 11,
            ],
            [
                'name' => 'Nikon',
                'slug' => 'nikon',
                'description' => 'Photography and imaging solutions',
                'logo' => null,
                'website_url' => 'https://www.nikon.com',
                'is_featured' => false,
                'status' => 1,
                'sort_order' => 12,
            ],
            [
                'name' => 'Bose',
                'slug' => 'bose',
                'description' => 'Premium audio equipment',
                'logo' => null,
                'website_url' => 'https://www.bose.com',
                'is_featured' => false,
                'status' => 1,
                'sort_order' => 13,
            ],
            [
                'name' => 'JBL',
                'slug' => 'jbl',
                'description' => 'Audio equipment and speakers',
                'logo' => null,
                'website_url' => 'https://www.jbl.com',
                'is_featured' => false,
                'status' => 1,
                'sort_order' => 14,
            ],
            [
                'name' => 'Microsoft',
                'slug' => 'microsoft',
                'description' => 'Software and hardware technology',
                'logo' => null,
                'website_url' => 'https://www.microsoft.com',
                'is_featured' => true,
                'status' => 1,
                'sort_order' => 15,
            ],
        ];

        foreach ($brands as $brandData) {
            Brand::updateOrCreate(
                ['slug' => $brandData['slug']],
                $brandData
            );
            
            echo "Created/Updated brand: {$brandData['name']}\n";
        }

        echo "✓ Brand seeding completed!\n";
    }
}
