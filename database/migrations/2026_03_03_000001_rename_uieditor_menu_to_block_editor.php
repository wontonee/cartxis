<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename "Visual Editor" to "Block Editor" in the admin menu.
     */
    public function up(): void
    {
        DB::table('menu_items')
            ->where('key', 'uieditor')
            ->update(['title' => 'Block Editor']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menu_items')
            ->where('key', 'uieditor')
            ->update(['title' => 'Visual Editor']);
    }
};
