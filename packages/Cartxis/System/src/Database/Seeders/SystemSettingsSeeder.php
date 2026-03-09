<?php

declare(strict_types=1);

namespace Cartxis\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['group' => 'system', 'key' => 'maintenance_enabled',     'value' => '0',                                                                    'type' => 'boolean'],
            ['group' => 'system', 'key' => 'maintenance_title',       'value' => "We'll be back soon!",                                                  'type' => 'text'],
            ['group' => 'system', 'key' => 'maintenance_message',     'value' => "We are performing scheduled maintenance. We'll be back online shortly.", 'type' => 'textarea'],
            ['group' => 'system', 'key' => 'maintenance_retry_after', 'value' => '3600',                                                                  'type' => 'number'],
            ['group' => 'system', 'key' => 'maintenance_secret',      'value' => '',                                                                      'type' => 'text'],
            ['group' => 'system', 'key' => 'maintenance_allowed_ips', 'value' => '[]',                                                                    'type' => 'json'],
            ['group' => 'system', 'key' => 'maintenance_start_time',  'value' => '',                                                                      'type' => 'datetime'],
            ['group' => 'system', 'key' => 'maintenance_end_time',    'value' => '',                                                                      'type' => 'datetime'],
            ['group' => 'system', 'key' => 'maintenance_bypass_admin','value' => '1',                                                                     'type' => 'boolean'],
            ['group' => 'system', 'key' => 'maintenance_contact_email','value' => '',                                                                     'type' => 'email'],
            ['group' => 'system', 'key' => 'maintenance_show_eta',    'value' => '1',                                                                     'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => $setting['group'], 'key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
