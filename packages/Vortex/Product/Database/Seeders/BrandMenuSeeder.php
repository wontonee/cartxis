<?php

namespace Vortex\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the Catalog parent menu item
        $catalogMenu = DB::table('menu_items')
            ->where('key', 'catalog')
            ->where('location', 'admin')
            ->first();

        if (!$catalogMenu) {
            // Create Catalog menu if it doesn't exist
            $catalogMenuId = DB::table('menu_items')->insertGetId([
                'key' => 'catalog',
                'title' => 'Catalog',
                'icon' => 'cube',
                'location' => 'admin',
                'active' => true,
                'order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $catalogMenuId = $catalogMenu->id;
        }

        // Check if Brands menu already exists
        $brandsMenuExists = DB::table('menu_items')
            ->where('key', 'catalog-brands')
            ->where('location', 'admin')
            ->exists();

        if (!$brandsMenuExists) {
            // Add Brands menu item under Catalog
            DB::table('menu_items')->insert([
                'key' => 'catalog-brands',
                'parent_id' => $catalogMenuId,
                'title' => 'Brands',
                'route' => 'admin.catalog.brands.index',
                'icon' => 'award',
                'location' => 'admin',
                'active' => true,
                'order' => 25,
                'extension_code' => 'vortex-product',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✓ Brands menu item added successfully!');
        } else {
            $this->command->info('✓ Brands menu item already exists.');
        }

        // Also check and add Attributes menu if missing
        $attributesMenuExists = DB::table('menu_items')
            ->where('key', 'catalog-attributes')
            ->where('location', 'admin')
            ->exists();

        if (!$attributesMenuExists) {
            DB::table('menu_items')->insert([
                'key' => 'catalog-attributes',
                'parent_id' => $catalogMenuId,
                'title' => 'Attributes',
                'route' => 'admin.catalog.attributes.index',
                'icon' => 'sliders',
                'location' => 'admin',
                'active' => true,
                'order' => 30,
                'extension_code' => 'vortex-product',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✓ Attributes menu item added successfully!');
        } else {
            $this->command->info('✓ Attributes menu item already exists.');
        }
    }
}
