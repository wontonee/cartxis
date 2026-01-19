<?php

namespace Cartxis\Core\Console\Commands;

use Illuminate\Console\Command;
use Cartxis\Core\Services\ExtensionService;

class ExtensionsInstallCommand extends Command
{
    protected $signature = 'cartxis:extensions:install {code : Extension code}';

    protected $aliases = ['vortex:extensions:install'];

    protected $description = 'Install a discovered filesystem extension (from the /extensions directory).';

    public function handle(ExtensionService $extensions): int
    {
        $code = (string) $this->argument('code');

        try {
            $extension = $extensions->install($code);
            $this->info("Installed: {$extension->code}");
            $this->line('Next: activate it with `php artisan cartxis:extensions:activate ' . $extension->code . '`');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
