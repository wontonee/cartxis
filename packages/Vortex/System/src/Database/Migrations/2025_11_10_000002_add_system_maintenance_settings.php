<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            [
                'group' => 'system',
                'key' => 'maintenance_enabled',
                'value' => '0',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'system',
                'key' => 'maintenance_title',
                'value' => 'We\'ll be back soon!',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'system',
                'key' => 'maintenance_message',
                'value' => 'We are performing scheduled maintenance. We\'ll be back online shortly.',
                'type' => 'textarea',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'system',
                'key' => 'maintenance_retry_after',
                'value' => '3600',
                'type' => 'number',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'system',
                'key' => 'maintenance_secret',
                'value' => '',
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'system',
                'key' => 'maintenance_allowed_ips',
                'value' => '[]',
                'type' => 'json',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'system',
                'key' => 'maintenance_start_time',
                'value' => '',
                'type' => 'datetime',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'system',
                'key' => 'maintenance_end_time',
                'value' => '',
                'type' => 'datetime',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'system',
                'key' => 'maintenance_bypass_admin',
                'value' => '1',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'system',
                'key' => 'maintenance_contact_email',
                'value' => '',
                'type' => 'email',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'system',
                'key' => 'maintenance_show_eta',
                'value' => '1',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('settings')->insert($settings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('group', 'system')
            ->whereIn('key', [
                'maintenance_enabled',
                'maintenance_title',
                'maintenance_message',
                'maintenance_retry_after',
                'maintenance_secret',
                'maintenance_allowed_ips',
                'maintenance_start_time',
                'maintenance_end_time',
                'maintenance_bypass_admin',
                'maintenance_contact_email',
                'maintenance_show_eta',
            ])
            ->delete();
    }
};
