<?php

namespace Cartxis\Shop\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\Core\Models\Theme;
use Cartxis\UIEditor\Models\PageLayout;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themePath = base_path('themes/cartxis-default');

        // ── 1. Theme record ────────────────────────────────────────────────
        $themeConfig = json_decode(file_get_contents($themePath . '/theme.json'), true);

        Theme::updateOrCreate(
            ['slug' => 'cartxis-default'],
            [
                'name'        => $themeConfig['name'],
                'description' => $themeConfig['description'],
                'version'     => $themeConfig['version'],
                'author'      => $themeConfig['author'],
                'screenshot'  => $themeConfig['screenshot'] ?? null,
                'is_active'   => true,
                'is_default'  => true,
                'settings'    => $themeConfig['settings'] ?? [],
            ]
        );

        $this->command->info('✓ Cartxis Default theme seeded successfully!');

        // ── 2. Homepage UIEditor layout ────────────────────────────────────
        // Only seed if NO published homepage layout exists yet.
        // This allows admins to customise the homepage without it being overwritten on re-seed.
        $existingPublished = PageLayout::homepage()->published()->first();

        if ($existingPublished) {
            $this->command->info('  ↳ Homepage layout already published — skipping.');
            return;
        }

        $themeDataPath = $themePath . '/data/theme-data.json';

        if (! file_exists($themeDataPath)) {
            $this->command->warn('  ↳ theme-data.json not found — homepage layout not seeded.');
            return;
        }

        $themeData   = json_decode(file_get_contents($themeDataPath), true);
        $homepageData = $themeData['homepage'] ?? null;

        if (! $homepageData) {
            $this->command->warn('  ↳ No [homepage] key in theme-data.json — skipping.');
            return;
        }

        // Delete any existing draft homepage layout before creating the seeded one
        PageLayout::homepage()->delete();

        PageLayout::create([
            'page_type'    => PageLayout::TYPE_HOMEPAGE,
            'page_id'      => null,
            'layout_data'  => $homepageData,
            'status'       => PageLayout::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->command->info('  ↳ Homepage layout seeded and published (' . count($homepageData['sections'] ?? []) . ' sections).');
    }
}
