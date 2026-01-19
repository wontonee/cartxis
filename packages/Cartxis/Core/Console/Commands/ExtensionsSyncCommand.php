<?php

namespace Cartxis\Core\Console\Commands;

use Illuminate\Console\Command;
use Cartxis\Core\Models\Extension;
use Cartxis\Core\Services\ExtensionService;

class ExtensionsSyncCommand extends Command
{
    protected $signature = 'cartxis:extensions:sync {--no-activate-bundled : Do not auto-activate bundled extensions when first syncing}';

    protected $aliases = ['vortex:extensions:sync'];

    protected $description = 'Sync discovered extension manifests into the extensions table.';

    public function handle(ExtensionService $extensions): int
    {
        $discovered = $extensions->discover();

        if ($discovered->isEmpty()) {
            $this->info('No extensions discovered.');
            return self::SUCCESS;
        }

        $created = 0;
        $updated = 0;

        foreach ($discovered as $item) {
            $manifest = $item['manifest'] ?? [];
            $code = $manifest['code'] ?? null;
            if (!$code) {
                continue;
            }

            $legacyCode = str_starts_with($code, 'cartxis-')
                ? 'vortex-' . substr($code, strlen('cartxis-'))
                : null;

            if ($legacyCode && !Extension::where('code', $code)->exists()) {
                Extension::where('code', $legacyCode)->update(['code' => $code]);
            }

            $extension = Extension::firstOrNew(['code' => $code]);
            $isNew = !$extension->exists;

            // Always refresh metadata from manifest.
            $extension->name = $manifest['name'] ?? $extension->name;
            $extension->description = $manifest['description'] ?? $extension->description;
            $extension->version = $manifest['version'] ?? $extension->version;
            $extension->author = $manifest['author'] ?? $extension->author;
            $extension->author_url = $manifest['author_url'] ?? $extension->author_url;
            $extension->icon = $manifest['icon'] ?? $extension->icon;
            $extension->requires = $manifest['requires'] ?? ($manifest['require'] ?? $extension->requires);

            // First sync defaults:
            if ($isNew) {
                $isBundled = ($item['source'] ?? null) === 'bundled';

                $extension->installed = $isBundled;
                $extension->installed_at = $isBundled ? now() : null;

                if ($isBundled && !$this->option('no-activate-bundled')) {
                    $extension->active = true;
                }
            }

            $extension->save();

            if ($isNew) {
                $created++;
            } else {
                $updated++;
            }
        }

        $this->info("Extensions synced. Created: {$created}. Updated: {$updated}.");
        $this->line("Tip: run `php artisan cartxis:extensions:list` to view status.");

        return self::SUCCESS;
    }
}
