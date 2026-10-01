<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('simple', 'configurable', 'virtual', 'downloadable', 'grouped', 'bundle', 'quote') NOT NULL DEFAULT 'simple'");
        }
        // SQLite / others: type is typically a string column — no ALTER needed.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('simple', 'configurable', 'virtual', 'downloadable', 'grouped', 'bundle') NOT NULL DEFAULT 'simple'");
        }
    }
};
