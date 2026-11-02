<?php

declare(strict_types=1);

namespace Cartxis\Core\Database\Seeders;

use Cartxis\Core\Models\Locale;
use Cartxis\Core\Services\LocaleService;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    public function run(): void
    {
        $locales = [
            // Global
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'is_default' => true, 'sort_order' => 1],
            // Middle East
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl', 'is_default' => false, 'sort_order' => 10],
            ['code' => 'he', 'name' => 'Hebrew', 'native_name' => 'עברית', 'direction' => 'rtl', 'is_default' => false, 'sort_order' => 11],
            ['code' => 'fa', 'name' => 'Persian', 'native_name' => 'فارسی', 'direction' => 'rtl', 'is_default' => false, 'sort_order' => 12],
            ['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe', 'direction' => 'ltr', 'is_default' => false, 'sort_order' => 13],
            // Europe
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'direction' => 'ltr', 'is_default' => false, 'sort_order' => 20],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'direction' => 'ltr', 'is_default' => false, 'sort_order' => 21],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'direction' => 'ltr', 'is_default' => false, 'sort_order' => 22],
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'direction' => 'ltr', 'is_default' => false, 'sort_order' => 23],
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português', 'direction' => 'ltr', 'is_default' => false, 'sort_order' => 24],
            ['code' => 'nl', 'name' => 'Dutch', 'native_name' => 'Nederlands', 'direction' => 'ltr', 'is_default' => false, 'sort_order' => 25],
            ['code' => 'pl', 'name' => 'Polish', 'native_name' => 'Polski', 'direction' => 'ltr', 'is_default' => false, 'sort_order' => 26],
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'direction' => 'ltr', 'is_default' => false, 'sort_order' => 27],
            ['code' => 'uk', 'name' => 'Ukrainian', 'native_name' => 'Українська', 'direction' => 'ltr', 'is_default' => false, 'sort_order' => 28],
            // Africa
            ['code' => 'sw', 'name' => 'Swahili', 'native_name' => 'Kiswahili', 'direction' => 'ltr', 'is_default' => false, 'sort_order' => 30],
            ['code' => 'am', 'name' => 'Amharic', 'native_name' => 'አማርኛ', 'direction' => 'ltr', 'is_default' => false, 'sort_order' => 31],
        ];

        foreach ($locales as $locale) {
            Locale::query()->updateOrCreate(
                ['code' => $locale['code']],
                [
                    'name' => $locale['name'],
                    'native_name' => $locale['native_name'],
                    'direction' => $locale['direction'],
                    'is_default' => $locale['is_default'],
                    'is_active' => true,
                    'sort_order' => $locale['sort_order'],
                ]
            );
        }

        // Ensure exactly one default
        if (! Locale::query()->where('is_default', true)->exists()) {
            Locale::query()->where('code', 'en')->update(['is_default' => true]);
        }

        app(LocaleService::class)->clearCache();
    }
}
