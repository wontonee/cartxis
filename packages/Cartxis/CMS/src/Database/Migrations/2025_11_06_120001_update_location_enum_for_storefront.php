<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change location enum to include 'storefront' (MySQL only, SQLite doesn't support MODIFY)
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE menu_items MODIFY COLUMN location ENUM('admin', 'shop', 'storefront') DEFAULT 'admin'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            // Normalize any storefront locations back to admin to avoid enum truncation errors
            DB::table('menu_items')
                ->where('location', 'storefront')
                ->update(['location' => 'admin']);

            DB::statement("ALTER TABLE menu_items MODIFY COLUMN location ENUM('admin', 'shop') DEFAULT 'admin'");
        }
    }
};
