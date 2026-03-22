<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix header "Deals" menu item — /deals 404s; /products?on_sale=1 is the correct route
        DB::table('menu_items')
            ->where('key', 'deals')
            ->where('location', 'storefront')
            ->update(['url' => '/products?on_sale=1', 'updated_at' => now()]);

        // Fix mobile nav "Deals" menu item
        DB::table('menu_items')
            ->where('key', 'mobile-deals')
            ->where('location', 'storefront')
            ->update(['url' => '/products?on_sale=1', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('menu_items')
            ->whereIn('key', ['deals', 'mobile-deals'])
            ->where('location', 'storefront')
            ->update(['url' => '/deals', 'updated_at' => now()]);
    }
};
