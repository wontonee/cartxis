<?php

namespace Vortex\Reports\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportsMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Check if Reports parent menu already exists
        $reportsMenu = DB::table('menu_items')
            ->where('title', 'Reports')
            ->where('parent_id', null)
            ->first();

        if (!$reportsMenu) {
            // Create Reports parent menu
            $reportsMenuId = DB::table('menu_items')->insertGetId([
                'title' => 'Reports',
                'icon' => 'bar-chart-2',
                'route' => null,
                'parent_id' => null,
                'order' => 50,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create Sales Reports child menu
            DB::table('menu_items')->insert([
                'title' => 'Sales Reports',
                'icon' => 'trending-up',
                'route' => 'admin.reports.sales',
                'parent_id' => $reportsMenuId,
                'order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create Product Reports child menu
            DB::table('menu_items')->insert([
                'title' => 'Product Reports',
                'icon' => 'package',
                'route' => 'admin.reports.products',
                'parent_id' => $reportsMenuId,
                'order' => 2,
                'is_active' => false, // Inactive until implemented
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create Customer Reports child menu
            DB::table('menu_items')->insert([
                'title' => 'Customer Reports',
                'icon' => 'users',
                'route' => 'admin.reports.customers',
                'parent_id' => $reportsMenuId,
                'order' => 3,
                'is_active' => false, // Inactive until implemented
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✓ Reports menu items created successfully!');
        } else {
            $this->command->info('Reports menu already exists.');
        }
    }
}
