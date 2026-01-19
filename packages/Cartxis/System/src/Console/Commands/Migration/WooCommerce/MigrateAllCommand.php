<?php

declare(strict_types=1);

namespace Cartxis\System\Console\Commands\Migration\WooCommerce;

use Illuminate\Console\Command;

class MigrateAllCommand extends Command
{
    protected $signature = 'wc:migrate:all {--dry-run : Preview migration without making changes} {--batch=500 : Number of records per batch}';
    
    protected $description = 'Migrate all WooCommerce data (categories, customers, products, orders)';
    
    public function handle(): int
    {
        $this->info('Starting WooCommerce migration...');
        $this->newLine();
        
        $dryRunOption = $this->option('dry-run') ? ['--dry-run' => true] : [];
        $batchOption = $this->option('batch') ? ['--batch' => $this->option('batch')] : [];
        
        // Migrate in order to maintain dependencies
        $commands = [
            ['command' => 'wc:migrate:categories', 'options' => $dryRunOption],
            ['command' => 'wc:migrate:customers', 'options' => array_merge($dryRunOption, $batchOption)],
            ['command' => 'wc:migrate:products', 'options' => array_merge($dryRunOption, $batchOption)],
            ['command' => 'wc:migrate:orders', 'options' => array_merge($dryRunOption, $batchOption)],
        ];
        
        foreach ($commands as $item) {
            $this->call($item['command'], $item['options']);
            $this->newLine();
        }
        
        if ($this->option('dry-run')) {
            $this->info('✓ Dry run completed. No changes were made.');
        } else {
            $this->info('✓ Migration completed successfully!');
        }
        
        return self::SUCCESS;
    }
}
