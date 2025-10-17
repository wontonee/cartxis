<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Packages\Core\Models\Theme;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load theme configuration
        $themePath = base_path('themes/vortex-default');
        $themeConfig = json_decode(file_get_contents($themePath . '/theme.json'), true);

        // Create or update default theme
        Theme::updateOrCreate(
            ['slug' => 'vortex-default'],
            [
                'name' => $themeConfig['name'],
                'description' => $themeConfig['description'],
                'version' => $themeConfig['version'],
                'author' => $themeConfig['author'],
                'screenshot' => $themeConfig['screenshot'] ?? null,
                'is_active' => true,
                'is_default' => true,
                'settings' => $themeConfig['settings'] ?? [],
            ]
        );

        $this->command->info('✓ Vortex Default theme seeded successfully!');
    }
}
