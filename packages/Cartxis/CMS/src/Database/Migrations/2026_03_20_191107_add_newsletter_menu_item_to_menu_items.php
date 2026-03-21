<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $marketingId = DB::table('menu_items')
            ->where('key', 'marketing')
            ->where('location', 'admin')
            ->value('id');

        // On a fresh install migrations run before seeders, so the marketing
        // parent may not exist yet. Skip insertion here — MarketingMenuSeeder
        // will create this item with the correct parent_id.
        if (! $marketingId) {
            return;
        }

        DB::table('menu_items')->updateOrInsert(
            ['key' => 'marketing-newsletters', 'location' => 'admin'],
            [
                'title'      => 'Newsletters',
                'icon'       => 'mail',
                'route'      => 'admin.marketing.newsletters.index',
                'url'        => null,
                'parent_id'  => $marketingId,
                'order'      => 30,
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menu_items')
            ->where('key', 'marketing-newsletters')
            ->where('location', 'admin')
            ->delete();
    }
};
