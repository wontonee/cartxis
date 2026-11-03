<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ensure ME / Europe / Africa locale rows exist for upgrades that skip db:seed.
 * Fresh installs already get these via LocaleSeeder; this migration is idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('locales')) {
            return;
        }

        $now = now();

        $locales = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'sort_order' => 1],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl', 'sort_order' => 10],
            ['code' => 'he', 'name' => 'Hebrew', 'native_name' => 'עברית', 'direction' => 'rtl', 'sort_order' => 11],
            ['code' => 'fa', 'name' => 'Persian', 'native_name' => 'فارسی', 'direction' => 'rtl', 'sort_order' => 12],
            ['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe', 'direction' => 'ltr', 'sort_order' => 13],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'direction' => 'ltr', 'sort_order' => 20],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'direction' => 'ltr', 'sort_order' => 21],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'direction' => 'ltr', 'sort_order' => 22],
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'direction' => 'ltr', 'sort_order' => 23],
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português', 'direction' => 'ltr', 'sort_order' => 24],
            ['code' => 'nl', 'name' => 'Dutch', 'native_name' => 'Nederlands', 'direction' => 'ltr', 'sort_order' => 25],
            ['code' => 'pl', 'name' => 'Polish', 'native_name' => 'Polski', 'direction' => 'ltr', 'sort_order' => 26],
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'direction' => 'ltr', 'sort_order' => 27],
            ['code' => 'uk', 'name' => 'Ukrainian', 'native_name' => 'Українська', 'direction' => 'ltr', 'sort_order' => 28],
            ['code' => 'sw', 'name' => 'Swahili', 'native_name' => 'Kiswahili', 'direction' => 'ltr', 'sort_order' => 30],
            ['code' => 'am', 'name' => 'Amharic', 'native_name' => 'አማርኛ', 'direction' => 'ltr', 'sort_order' => 31],
        ];

        foreach ($locales as $locale) {
            $existing = DB::table('locales')->where('code', $locale['code'])->first();

            if ($existing) {
                // Refresh catalog metadata only — never reset merchant default / active flags.
                DB::table('locales')->where('code', $locale['code'])->update([
                    'name' => $locale['name'],
                    'native_name' => $locale['native_name'],
                    'direction' => $locale['direction'],
                    'sort_order' => $locale['sort_order'],
                    'updated_at' => $now,
                ]);

                continue;
            }

            DB::table('locales')->insert([
                'code' => $locale['code'],
                'name' => $locale['name'],
                'native_name' => $locale['native_name'],
                'direction' => $locale['direction'],
                'is_default' => false,
                'is_active' => true,
                'sort_order' => $locale['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! DB::table('locales')->where('is_default', true)->exists()) {
            DB::table('locales')->where('code', 'en')->update([
                'is_default' => true,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Keep locale rows — merchants may have activated / customized them.
    }
};
