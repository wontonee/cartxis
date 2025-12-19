<?php

namespace Vortex\Core\Console\Commands;

use Illuminate\Console\Command;
use Vortex\Core\Services\ExtensionService;

class ExtensionsUninstallCommand extends Command
{
    protected $signature = 'vortex:extensions:uninstall {code : Extension code}';

    protected $description = 'Uninstall an extension.';

    public function handle(ExtensionService $extensions): int
    {
        $code = (string) $this->argument('code');

        try {
            $extensions->uninstall($code);
            $this->info("Uninstalled: {$code}");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
