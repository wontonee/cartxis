<?php

namespace Cartxis\Core\Console\Commands;

use Illuminate\Console\Command;
use Cartxis\Core\Models\Theme;
use Cartxis\Core\Services\ThemeService;

class ThemeListCommand extends Command
{
    protected $signature = 'theme:list';

    protected $description = 'List all registered themes and their status';

    public function handle(ThemeService $themeService): int
    {
        // Discover first so the list is always up-to-date
        $themeService->discover();

        $themes = Theme::orderBy('name')->get();

        if ($themes->isEmpty()) {
            $this->components->warn('No themes registered. Place a theme in themes/ and run theme:discover.');
            return self::SUCCESS;
        }

        $rows = $themes->map(fn (Theme $t) => [
            $t->slug,
            $t->name,
            $t->version,
            $t->author ?: '—',
            $t->is_active ? '<fg=green>● active</>' : '<fg=gray>inactive</>',
            $t->is_default ? 'yes' : '',
            $t->exists() ? '<fg=green>yes</>' : '<fg=red>MISSING</>',
        ]);

        $this->table(
            ['Slug', 'Name', 'Version', 'Author', 'Status', 'Default', 'Files'],
            $rows->toArray()
        );

        return self::SUCCESS;
    }
}
