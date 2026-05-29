<?php

namespace Cartxis\Core\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Cartxis\Core\Services\TemplateInstallService;

class TemplateInstallCommand extends Command
{
    protected $signature = 'template:install
                            {slug : Template slug from the catalog}
                            {--activate : Activate the theme after install}
                            {--import-layout : Import demo homepage layout}
                            {--import-demo-products : Import vertical demo products when supported}';

    protected $description = 'Install a storefront template from the catalog into templates/storefront/';

    public function handle(TemplateInstallService $installer): int
    {
        $slug = (string) $this->argument('slug');

        try {
            $result = $installer->install($slug, [
                'activate' => (bool) $this->option('activate'),
                'import_layout' => (bool) $this->option('import-layout'),
                'import_demo_products' => (bool) $this->option('import-demo-products'),
            ]);

            $this->components->info("Template \"{$slug}\" installed.");

            if ($result['activated']) {
                $this->components->info('Theme activated.');
            }

            if ($result['layout_imported']) {
                $this->components->info('Demo layout imported.');
            }

            if ($result['demo_products_imported']) {
                $this->components->info('Demo products imported.');
            }

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
