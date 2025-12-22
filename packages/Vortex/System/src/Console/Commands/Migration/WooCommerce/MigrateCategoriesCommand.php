<?php

declare(strict_types=1);

namespace Vortex\System\Console\Commands\Migration\WooCommerce;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Vortex\Product\Models\Category;

class MigrateCategoriesCommand extends Command
{
    protected $signature = 'wc:migrate:categories {--dry-run : Preview migration without making changes}';
    
    protected $description = 'Migrate WooCommerce product categories to Vortex';
    
    private array $errors = [];
    
    public function handle(): int
    {
        $config = config('woocommerce-migration');
        $prefix = $config['database']['prefix'];
        
        // Get categories from WooCommerce
        $categories = DB::connection($config['connection'])
            ->table($prefix . 'terms as t')
            ->join($prefix . 'term_taxonomy as tt', 't.term_id', '=', 'tt.term_id')
            ->where('tt.taxonomy', 'product_cat')
            ->select('t.term_id', 't.name', 't.slug', 'tt.description', 'tt.parent', 'tt.count')
            ->get();
        
        if ($categories->isEmpty()) {
            $this->warn('No categories found to migrate.');
            return self::SUCCESS;
        }
        
        $this->info("Found {$categories->count()} categories to migrate.");
        
        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Name', 'Slug', 'Parent ID', 'Products'],
                $categories->map(fn($cat) => [
                    $cat->term_id,
                    $cat->name,
                    $cat->slug,
                    $cat->parent ?: '-',
                    $cat->count,
                ])->toArray()
            );
            return self::SUCCESS;
        }
        
        $this->newLine();
        $bar = $this->output->createProgressBar($categories->count());
        $bar->start();
        
        foreach ($categories as $wcCategory) {
            try {
                Category::updateOrCreate(
                    ['slug' => $wcCategory->slug],
                    [
                        'name' => $wcCategory->name,
                        'description' => $wcCategory->description,
                        'parent_id' => $wcCategory->parent ?: null,
                        'status' => 'enabled',
                        'meta' => [
                            'woocommerce_id' => $wcCategory->term_id,
                            'product_count' => $wcCategory->count,
                        ],
                    ]
                );
            } catch (\Exception $e) {
                $this->errors[] = "Category '{$wcCategory->name}': {$e->getMessage()}";
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        if (empty($this->errors)) {
            $this->info("✓ Successfully migrated {$categories->count()} categories.");
        } else {
            $this->warn("Migrated with " . count($this->errors) . " errors:");
            foreach ($this->errors as $error) {
                $this->error("  - {$error}");
            }
        }
        
        return self::SUCCESS;
    }
}
