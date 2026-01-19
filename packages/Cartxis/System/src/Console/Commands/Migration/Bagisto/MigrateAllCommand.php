<?php

declare(strict_types=1);

namespace Cartxis\System\Console\Commands\Migration\Bagisto;

use Illuminate\Console\Command;

class MigrateAllCommand extends Command
{
    protected $signature = 'bagisto:migrate:all {--dry-run : Preview migration without making changes} {--batch=500 : Number of records per batch}';
    
    protected $description = 'Migrate all Bagisto data (categories, customers, products, orders)';
    
    public function handle(): int
    {
        $this->info('Starting Bagisto migration...');
        $this->newLine();
        
        $dryRunOption = $this->option('dry-run') ? ['--dry-run' => true] : [];
        $batchOption = $this->option('batch') ? ['--batch' => $this->option('batch')] : [];
        
        // Migrate in order to maintain dependencies
        $commands = [
            ['command' => 'bagisto:migrate:categories', 'options' => $dryRunOption],
            ['command' => 'bagisto:migrate:customers', 'options' => array_merge($dryRunOption, $batchOption)],
            ['command' => 'bagisto:migrate:products', 'options' => array_merge($dryRunOption, $batchOption)],
            ['command' => 'bagisto:migrate:orders', 'options' => array_merge($dryRunOption, $batchOption)],
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
