<?php

namespace Vortex\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Vortex\Product\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and accessories',
                'children' => [
                    ['name' => 'Computers', 'description' => 'Laptops, desktops, and computer accessories'],
                    ['name' => 'Audio', 'description' => 'Headphones, speakers, and audio equipment'],
                    ['name' => 'Mobile Devices', 'description' => 'Phones, tablets, and accessories'],
                ],
            ],
            [
                'name' => 'Clothing',
                'description' => 'Apparel for men, women, and children',
                'children' => [
                    ['name' => 'Men\'s Clothing', 'description' => 'Shirts, pants, and accessories for men'],
                    ['name' => 'Women\'s Clothing', 'description' => 'Dresses, tops, and accessories for women'],
                    ['name' => 'Kids Clothing', 'description' => 'Clothing for boys and girls'],
                ],
            ],
            [
                'name' => 'Home & Garden',
                'description' => 'Home decor and garden supplies',
                'children' => [
                    ['name' => 'Furniture', 'description' => 'Living room, bedroom, and office furniture'],
                    ['name' => 'Kitchen', 'description' => 'Kitchen appliances and utensils'],
                    ['name' => 'Garden', 'description' => 'Gardening tools and outdoor equipment'],
                ],
            ],
            [
                'name' => 'Sports & Outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'children' => [
                    ['name' => 'Fitness', 'description' => 'Gym equipment and fitness accessories'],
                    ['name' => 'Camping', 'description' => 'Tents, sleeping bags, and camping gear'],
                    ['name' => 'Sports Equipment', 'description' => 'Balls, rackets, and sporting goods'],
                ],
            ],
            [
                'name' => 'Books',
                'description' => 'Books and magazines',
                'children' => [
                    ['name' => 'Fiction', 'description' => 'Novels and fiction literature'],
                    ['name' => 'Non-Fiction', 'description' => 'Biographies, history, and educational books'],
                    ['name' => 'Magazines', 'description' => 'Periodicals and magazines'],
                ],
            ],
            [
                'name' => 'Accessories',
                'description' => 'Fashion and lifestyle accessories',
                'children' => [
                    ['name' => 'Bags', 'description' => 'Backpacks, handbags, and travel bags'],
                    ['name' => 'Watches', 'description' => 'Wristwatches and smartwatches'],
                    ['name' => 'Jewelry', 'description' => 'Necklaces, rings, and earrings'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            // Create parent category
            $parent = Category::create([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'description' => $categoryData['description'],
                'status' => 'enabled',
                'sort_order' => 0,
                'meta_title' => $categoryData['name'] . ' - Vortex Commerce',
                'meta_description' => $categoryData['description'],
            ]);

            $this->command->info("Created category: {$parent->name}");

            // Create children categories
            foreach ($children as $childData) {
                $child = Category::create([
                    'name' => $childData['name'],
                    'slug' => Str::slug($childData['name']),
                    'description' => $childData['description'],
                    'status' => 'enabled',
                    'parent_id' => $parent->id,
                    'sort_order' => 0,
                    'meta_title' => $childData['name'] . ' - Vortex Commerce',
                    'meta_description' => $childData['description'],
                ]);

                $this->command->info("  Created subcategory: {$child->name}");
            }
        }

        $this->command->info('Category seeding completed!');
    }
}
