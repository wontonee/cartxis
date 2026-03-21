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
        // Remove the stale "Newsletters" admin menu item that has no backing route.
        DB::table('menu_items')
            ->where('key', 'marketing-newsletters')
            ->orWhere(function ($q) {
                $q->where('location', 'admin')
                  ->whereRaw("LOWER(title) = 'newsletters'")
                  ->whereNotNull('route')
                  ->where('route', 'like', '%newsletter%');
            })
            ->delete();

        // Catch-all: deactivate any admin menu item pointing to the missing route.
        DB::table('menu_items')
            ->where('location', 'admin')
            ->where('route', 'admin.marketing.newsletters.index')
            ->update(['active' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is intentionally irreversible.
        // The 'marketing-newsletters' menu item was a stale DB record with no
        // backing route, controller, or Vue page. Re-inserting it would restore
        // a broken 404 link. If you need to roll back, restore it manually.
        throw new \RuntimeException(
            'Migration 2026_03_20_182908_remove_stale_newsletter_menu_item is irreversible. '
            . 'Re-inserting the stale newsletter menu item would reintroduce a 404 link.'
        );
    }
};
