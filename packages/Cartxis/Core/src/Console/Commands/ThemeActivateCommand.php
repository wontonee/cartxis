<?php

namespace Cartxis\Core\Console\Commands;

use Illuminate\Console\Command;
use Cartxis\Core\Models\Theme;
use Cartxis\Core\Services\ThemeService;
use Cartxis\UIEditor\Models\PageLayout;
use Cartxis\UIEditor\Services\LayoutService;

class ThemeActivateCommand extends Command
{
    protected $signature = 'theme:activate {slug : The theme slug to activate} {--import-layout : Also import and publish the theme demo homepage layout}';

    protected $description = 'Activate a registered theme by its slug';

    public function handle(ThemeService $themeService, LayoutService $layoutService): int
    {
        $slug = $this->argument('slug');

        $theme = Theme::where('slug', $slug)->first();

        if (! $theme) {
            // Try discovering first in case the theme was just added
            $themeService->discover();
            $theme = Theme::where('slug', $slug)->first();
        }

        if (! $theme) {
            $this->components->error("Theme '{$slug}' not found. Run theme:list to see available themes.");
            return self::FAILURE;
        }

        if (! $theme->exists()) {
            $this->components->error("Theme files for '{$slug}' are missing at: " . $theme->getPath());
            return self::FAILURE;
        }

        if ($theme->is_active) {
            $this->components->info("Theme '{$theme->name}' is already active.");
            return self::SUCCESS;
        }

        $result = $themeService->activate($slug);

        if ($result) {
            $this->components->info("Theme '{$theme->name}' activated successfully!");

            if ($this->option('import-layout')) {
                $dataPath = $theme->getPath() . '/data/theme-data.json';
                if (!file_exists($dataPath)) {
                    $this->components->warn('No theme-data.json found — skipping layout import.');
                } else {
                    $themeData = json_decode(file_get_contents($dataPath), true);
                    if (empty($themeData['homepage'])) {
                        $this->components->warn('No homepage layout in theme-data.json — skipping.');
                    } elseif (PageLayout::homepage()->published()->exists()) {
                        $this->components->info('A published homepage layout already exists — skipping layout import.');
                    } else {
                        $layout = $layoutService->saveDraft($themeData['homepage'], PageLayout::TYPE_HOMEPAGE, null);
                        $layoutService->publish($layout);
                        $this->components->info('Homepage layout imported and published.');
                    }
                }
            }
        } else {
            $this->components->error('Failed to activate theme.');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
