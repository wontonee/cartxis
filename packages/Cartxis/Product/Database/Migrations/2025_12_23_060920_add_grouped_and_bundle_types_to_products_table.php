<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the type enum to add 'grouped' and 'bundle' types (MySQL only)
        if (\DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('This migration supports MySQL only.');
        }

        \DB::statement("ALTER TABLE `products` MODIFY COLUMN `type` ENUM('simple', 'configurable', 'virtual', 'downloadable', 'grouped', 'bundle') NOT NULL DEFAULT 'simple'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original types (MySQL only)
        if (\DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('This migration supports MySQL only.');
        }

        \DB::statement("ALTER TABLE `products` MODIFY COLUMN `type` ENUM('simple', 'configurable', 'virtual', 'downloadable') NOT NULL DEFAULT 'simple'");
    }
};
