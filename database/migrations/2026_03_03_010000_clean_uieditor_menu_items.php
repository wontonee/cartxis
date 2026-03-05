<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove the "Block Editor" sidebar item (shared URL with Pages → dual-highlight)
        DB::table('menu_items')->whereIn('key', ['uieditor', 'uieditor-homepage'])->delete();

        // Rename CMS "Blocks" → "Snippets" to free up the "Blocks" name for UIEditor reusables
        DB::table('menu_items')
            ->where('key', 'content-blocks')
            ->update(['title' => 'Snippets']);
    }

    public function down(): void
    {
        DB::table('menu_items')
            ->where('key', 'content-blocks')
            ->update(['title' => 'Blocks']);
    }
};
