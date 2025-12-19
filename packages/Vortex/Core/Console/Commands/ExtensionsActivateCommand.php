<?php

namespace Vortex\Core\Console\Commands;

use Illuminate\Console\Command;
use Vortex\Core\Services\ExtensionService;

class ExtensionsActivateCommand extends Command
{
    protected $signature = 'vortex:extensions:activate {code : Extension code}';

    protected $description = 'Activate an installed extension.';

    public function handle(ExtensionService $extensions): int
    {
        $code = (string) $this->argument('code');

        try {
            $extension = $extensions->activate($code);
            $this->info("Activated: {$extension->code}");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
