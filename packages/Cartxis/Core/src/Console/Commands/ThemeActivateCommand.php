<?php

namespace Cartxis\Core\Console\Commands;

use Illuminate\Console\Command;
use Cartxis\Core\Models\Theme;
use Cartxis\Core\Services\ThemeService;

class ThemeActivateCommand extends Command
{
    protected $signature = 'theme:activate {slug : The theme slug to activate}';

    protected $description = 'Activate a registered theme by its slug';

    public function handle(ThemeService $themeService): int
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
        } else {
            $this->components->error('Failed to activate theme.');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
