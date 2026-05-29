<?php

namespace Cartxis\Core\Console\Commands;

use Cartxis\Core\Models\Theme;
use Cartxis\Core\Services\ThemeDataImportService;
use Illuminate\Console\Command;

class ThemeImportDataCommand extends Command
{
    protected $signature = 'theme:import-data
        {slug : The theme slug to import data for}
        {--fresh : Remove existing theme data before importing}
        {--blocks : Import only CMS blocks}
        {--menus : Import only storefront menus}
        {--settings : Import only theme settings}';

    protected $description = 'Import demo data (CMS blocks, menus, settings) from a theme\'s data directory';

    public function handle(ThemeDataImportService $importService): int
    {
        $slug = $this->argument('slug');

        if (! Theme::where('slug', $slug)->exists()) {
            $this->components->error("Theme '{$slug}' not found. Run theme:list to see available themes.");

            return self::FAILURE;
        }

        $importAll = ! $this->option('blocks') && ! $this->option('menus') && ! $this->option('settings');

        if ($this->option('fresh')) {
            if (! $this->confirm('This will delete existing theme-specific data. Continue?', true)) {
                $this->components->warn('Import cancelled.');

                return self::SUCCESS;
            }
        }

        try {
            $theme = Theme::where('slug', $slug)->first();
            $this->components->info("Importing data for theme: {$theme->name}");
            $this->newLine();

            $results = $importService->import(
                $slug,
                blocks: $importAll || (bool) $this->option('blocks'),
                menus: $importAll || (bool) $this->option('menus'),
                settings: $importAll || (bool) $this->option('settings'),
                fresh: (bool) $this->option('fresh'),
            );
        } catch (\Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->components->info('Import Summary:');
        $this->components->twoColumnDetail('CMS Blocks', "<fg=green>{$results['blocks']} imported</>");
        $this->components->twoColumnDetail('Menu Items', "<fg=green>{$results['menus']} imported</>");
        $this->components->twoColumnDetail(
            'Theme Settings',
            $results['settings'] ? '<fg=green>updated</>' : '<fg=gray>skipped</>'
        );
        $this->newLine();
        $this->components->info('Theme data imported successfully!');

        return self::SUCCESS;
    }
}
