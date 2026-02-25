<?php

namespace Cartxis\Core\Console\Commands;

use Illuminate\Console\Command;
use Cartxis\Core\Services\ThemeService;

class ThemeDiscoverCommand extends Command
{
    protected $signature = 'theme:discover';

    protected $description = 'Scan the themes/ directory and register any new themes into the database';

    public function handle(ThemeService $themeService): int
    {
        $this->components->info('Discovering themes…');

        $discovered = $themeService->discover();

        if (empty($discovered)) {
            $this->components->warn('No themes found in ' . base_path('themes'));
            return self::SUCCESS;
        }

        foreach ($discovered as $item) {
            $this->components->twoColumnDetail(
                $item['config']['name'] ?? $item['slug'],
                '<fg=green>registered</>'
            );
        }

        $this->newLine();
        $this->components->info(count($discovered) . ' theme(s) discovered.');

        return self::SUCCESS;
    }
}
