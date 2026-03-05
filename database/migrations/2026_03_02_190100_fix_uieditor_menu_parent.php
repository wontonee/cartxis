<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Move UIEditor menu items under the Content parent.
     */
    public function up(): void
    {
        $contentId = DB::table('menu_items')->where('key', 'content')->value('id');

        if ($contentId) {
            DB::table('menu_items')
                ->whereIn('key', ['uieditor', 'uieditor-homepage'])
                ->update(['parent_id' => $contentId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menu_items')
            ->whereIn('key', ['uieditor', 'uieditor-homepage'])
            ->update(['parent_id' => null]);
    }
};
