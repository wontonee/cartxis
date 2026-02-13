<?php

namespace Cartxis\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Cartxis\Core\Models\Theme;
use Cartxis\CMS\Models\Block;

class ThemeImportDataCommand extends Command
{
    protected $signature = 'theme:import-data
        {slug : The theme slug to import data for}
        {--fresh : Remove existing theme data before importing}
        {--blocks : Import only CMS blocks}
        {--menus : Import only storefront menus}
        {--settings : Import only theme settings}';

    protected $description = 'Import demo data (CMS blocks, menus, settings) from a theme\'s data directory';

    public function handle(): int
    {
        $slug = $this->argument('slug');

        // Verify theme exists
        $theme = Theme::where('slug', $slug)->first();
        if (! $theme) {
            $this->components->error("Theme '{$slug}' not found. Run theme:list to see available themes.");
            return self::FAILURE;
        }

        // Verify data file exists
        $dataPath = $theme->getPath() . '/data/theme-data.json';
        if (! file_exists($dataPath)) {
            $this->components->error("No data file found at: {$dataPath}");
            $this->components->info('Create a data/theme-data.json file in your theme directory.');
            return self::FAILURE;
        }

        $data = json_decode(file_get_contents($dataPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->components->error('Invalid JSON in theme-data.json: ' . json_last_error_msg());
            return self::FAILURE;
        }

        $importAll = ! $this->option('blocks') && ! $this->option('menus') && ! $this->option('settings');

        $this->components->info("Importing data for theme: {$theme->name}");
        $this->newLine();

        // Handle --fresh option
        if ($this->option('fresh')) {
            if ($this->confirm('This will delete existing theme-specific data. Continue?', true)) {
                $this->cleanExistingData($slug, $data, $importAll);
            } else {
                $this->components->warn('Import cancelled.');
                return self::SUCCESS;
            }
        }

        $results = ['blocks' => 0, 'menus' => 0, 'settings' => false];

        // Import CMS Blocks
        if ($importAll || $this->option('blocks')) {
            $results['blocks'] = $this->importBlocks($data['blocks'] ?? [], $slug);
        }

        // Import Menus
        if ($importAll || $this->option('menus')) {
            $results['menus'] = $this->importMenus($data['menus'] ?? [], $slug);
        }

        // Import Theme Settings
        if ($importAll || $this->option('settings')) {
            $results['settings'] = $this->importSettings($data['settings'] ?? [], $theme);
        }

        // Summary
        $this->newLine();
        $this->components->info('Import Summary:');
        $this->components->twoColumnDetail('CMS Blocks', "<fg=green>{$results['blocks']} imported</>");
        $this->components->twoColumnDetail('Menu Items', "<fg=green>{$results['menus']} imported</>");
        $this->components->twoColumnDetail('Theme Settings', $results['settings'] ? '<fg=green>updated</>' : '<fg=gray>skipped</>');
        $this->newLine();
        $this->components->info('Theme data imported successfully!');

        return self::SUCCESS;
    }

    /**
     * Import CMS blocks from theme data.
     * Uses updateOrCreate by identifier — safe to run multiple times.
     */
    protected function importBlocks(array $blocks, string $themeSlug): int
    {
        $count = 0;

        foreach ($blocks as $blockData) {
            $identifier = $blockData['identifier'];

            Block::withTrashed()->updateOrCreate(
                ['identifier' => $identifier],
                [
                    'title'      => $blockData['title'],
                    'type'       => $blockData['type'] ?? 'html',
                    'content'    => is_array($blockData['content'])
                        ? json_encode($blockData['content'])
                        : $blockData['content'],
                    'status'     => $blockData['status'] ?? 'active',
                    'deleted_at' => null, // Restore if soft-deleted
                ]
            );

            $this->components->twoColumnDetail(
                "  Block: {$identifier}",
                '<fg=green>✓</>'
            );
            $count++;
        }

        return $count;
    }

    /**
     * Import storefront menus from theme data.
     * Prefixes menu keys with theme slug to avoid conflicts.
     */
    protected function importMenus(array $menus, string $themeSlug): int
    {
        $count = 0;

        foreach ($menus as $menuType => $items) {
            $this->components->twoColumnDetail(
                "  Menu: {$menuType}",
                '<fg=cyan>processing</>'
            );

            foreach ($items as $item) {
                $count += $this->createMenuItem($item, $menuType, $themeSlug, null);
            }
        }

        return $count;
    }

    /**
     * Recursively create a menu item and its children.
     */
    protected function createMenuItem(array $item, string $menuType, string $themeSlug, ?int $parentId): int
    {
        $count = 0;
        $key = "{$themeSlug}-{$menuType}-" . \Illuminate\Support\Str::slug($item['title']);

        // Check if menu item already exists
        $existing = DB::table('menu_items')->where('key', $key)->first();

        $data = [
            'title'     => $item['title'],
            'key'       => $key,
            'icon'      => $item['icon'] ?? null,
            'route'     => $item['route'] ?? null,
            'url'       => $item['url'] ?? null,
            'location'  => 'storefront',
            'menu_type' => $menuType,
            'parent_id' => $parentId,
            'order'     => $item['order'] ?? 0,
            'active'    => $item['active'] ?? true,
            'meta'      => isset($item['meta']) ? json_encode($item['meta']) : null,
        ];

        if ($existing) {
            DB::table('menu_items')->where('id', $existing->id)->update(
                array_merge($data, ['updated_at' => now()])
            );
            $itemId = $existing->id;
        } else {
            $itemId = DB::table('menu_items')->insertGetId(
                array_merge($data, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $count++;

        // Process children recursively
        if (! empty($item['children'])) {
            foreach ($item['children'] as $child) {
                $count += $this->createMenuItem($child, $menuType, $themeSlug, $itemId);
            }
        }

        return $count;
    }

    /**
     * Import theme settings into the Theme model's settings JSON column.
     */
    protected function importSettings(array $settings, Theme $theme): bool
    {
        if (empty($settings)) {
            return false;
        }

        // Merge with existing settings (theme data doesn't overwrite admin customizations)
        $existingSettings = $theme->settings ?? [];
        $mergedSettings = array_replace_recursive($existingSettings, $settings);

        $theme->update(['settings' => $mergedSettings]);

        $this->components->twoColumnDetail(
            '  Settings',
            '<fg=green>' . count($settings) . ' keys merged</>'
        );

        return true;
    }

    /**
     * Clean existing theme data before fresh import.
     */
    protected function cleanExistingData(string $themeSlug, array $data, bool $importAll): void
    {
        // Clean blocks
        if ($importAll || $this->option('blocks')) {
            $identifiers = collect($data['blocks'] ?? [])->pluck('identifier')->toArray();
            if (! empty($identifiers)) {
                $deleted = Block::withTrashed()->whereIn('identifier', $identifiers)->forceDelete();
                $this->components->warn("Removed {$deleted} existing block(s).");
            }
        }

        // Clean menus
        if ($importAll || $this->option('menus')) {
            $deleted = DB::table('menu_items')
                ->where('key', 'like', "{$themeSlug}-%")
                ->delete();
            $this->components->warn("Removed {$deleted} existing menu item(s).");
        }

        // Settings are merged, so nothing to clean unless we reset completely
        if ($importAll || $this->option('settings')) {
            $theme = Theme::where('slug', $themeSlug)->first();
            if ($theme) {
                $theme->update(['settings' => []]);
                $this->components->warn('Reset theme settings.');
            }
        }
    }
}
