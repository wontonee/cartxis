<?php

namespace Cartxis\Core\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Cartxis\Core\Services\TemplateInstallService;

class TemplateExportCommand extends Command
{
    protected $signature = 'template:export
                            {slug : Template slug}
                            {--source=catalog : Export source: catalog or installed}
                            {--output= : Optional output zip path}';

    protected $description = 'Export a template package as a zip archive';

    public function handle(TemplateInstallService $installer): int
    {
        $slug = (string) $this->argument('slug');
        $source = (string) $this->option('source');

        try {
            $export = $installer->export($slug, $source);
            $output = $this->option('output');

            if (is_string($output) && $output !== '') {
                if (! @copy($export['path'], $output)) {
                    throw new Exception('Unable to write export to the requested output path.');
                }

                @unlink($export['path']);
                $this->components->info("Template exported to {$output}");
            } else {
                $this->components->info("Template exported to {$export['path']}");
            }

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
