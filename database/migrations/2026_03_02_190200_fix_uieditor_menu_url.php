<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Change Visual Editor menu item to use a direct URL instead of a
     * parameterised route that can never resolve without a {page} id.
     */
    public function up(): void
    {
        DB::table('menu_items')
            ->where('key', 'uieditor')
            ->update([
                'route' => null,
                'url'   => '/admin/content/pages',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menu_items')
            ->where('key', 'uieditor')
            ->update([
                'route' => 'admin.uieditor.pages.editor',
                'url'   => null,
            ]);
    }
};
