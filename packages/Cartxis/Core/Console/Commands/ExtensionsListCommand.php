<?php

namespace Cartxis\Core\Console\Commands;

use Illuminate\Console\Command;
use Cartxis\Core\Models\Extension;
use Cartxis\Core\Services\ExtensionService;

class ExtensionsListCommand extends Command
{
    protected $signature = 'cartxis:extensions:list {--installed : Show only installed extensions} {--active : Show only active extensions}';

    protected $aliases = ['vortex:extensions:list'];

    protected $description = 'List discovered Cartxis extensions and their install/active status.';

    public function handle(ExtensionService $extensions): int
    {
        $discovered = $extensions->discover();

        $rows = $discovered->map(function (array $item) {
            $manifest = $item['manifest'] ?? [];
            $code = $manifest['code'] ?? '';

            $db = Extension::where('code', $code)->first();

            return [
                'code' => $code,
                'name' => $manifest['name'] ?? ($db?->name ?? ''),
                'version' => $manifest['version'] ?? ($db?->version ?? ''),
                'source' => $item['source'] ?? 'unknown',
                'installed' => $db?->installed ? 'yes' : 'no',
                'active' => $db?->active ? 'yes' : 'no',
                'path' => $item['path'] ?? '',
            ];
        });

        if ($this->option('installed')) {
            $rows = $rows->where('installed', 'yes');
        }

        if ($this->option('active')) {
            $rows = $rows->where('active', 'yes');
        }

        if ($rows->isEmpty()) {
            $this->info('No extensions discovered.');
            return self::SUCCESS;
        }

        $this->table(
            ['code', 'name', 'version', 'source', 'installed', 'active', 'path'],
            $rows->values()->all()
        );

        return self::SUCCESS;
    }
}
