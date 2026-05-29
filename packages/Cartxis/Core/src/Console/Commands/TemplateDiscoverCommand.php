<?php

namespace Cartxis\Core\Console\Commands;

use Illuminate\Console\Command;
use Cartxis\Core\Services\TemplateCatalogService;

class TemplateDiscoverCommand extends Command
{
    protected $signature = 'template:discover {--validate : Fail when catalog entries are invalid}';

    protected $description = 'Scan the templates/ catalog and list available template packages';

    public function handle(TemplateCatalogService $catalog): int
    {
        if ($this->option('validate')) {
            $errors = $catalog->validateCatalog();

            if (! empty($errors)) {
                foreach ($errors as $error) {
                    $this->components->error($error);
                }

                return self::FAILURE;
            }
        }

        $this->components->info('Discovering template catalog…');

        $entries = $catalog->discover();

        if ($entries->isEmpty()) {
            $this->components->warn('No templates found in ' . config('theme.catalog_path'));
            return self::SUCCESS;
        }

        foreach ($entries as $entry) {
            $status = ($entry['installed'] ?? false) ? 'installed' : 'available';
            $label = sprintf(
                '%s [%s/%s]',
                $entry['name'] ?? $entry['slug'],
                $entry['type'] ?? 'unknown',
                $entry['category'] ?? 'uncategorized'
            );

            $this->components->twoColumnDetail($label, "<fg=green>{$status}</>");
        }

        $this->newLine();
        $this->components->info($entries->count() . ' template(s) discovered.');

        return self::SUCCESS;
    }
}
