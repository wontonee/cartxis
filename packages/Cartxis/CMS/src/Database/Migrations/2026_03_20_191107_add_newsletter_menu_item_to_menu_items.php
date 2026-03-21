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
