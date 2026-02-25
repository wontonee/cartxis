<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('menu_items')) {
            return;
        }

        $now = now();

        DB::table('menu_items')->updateOrInsert(
            ['key' => 'system'],
            [
                'title' => 'System',
                'icon' => 'server',
                'route' => null,
                'parent_id' => null,
                'order' => 90,
                'location' => 'admin',
                'active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $systemId = DB::table('menu_items')->where('key', 'system')->value('id');

        if (!$systemId) {
            return;
        }

        DB::table('menu_items')->updateOrInsert(
            ['key' => 'system-activity-logs'],
            [
                'title' => 'Activity Logs',
                'icon' => 'list',
                'route' => 'admin.activity-logs.index',
                'parent_id' => $systemId,
                'order' => 9,
                'location' => 'admin',
                'active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('menu_items')) {
            return;
        }

        DB::table('menu_items')->where('key', 'system-activity-logs')->delete();
    }
};
