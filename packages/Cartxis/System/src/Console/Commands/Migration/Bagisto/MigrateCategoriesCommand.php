<?php

declare(strict_types=1);

namespace Cartxis\System\Console\Commands\Migration\Bagisto;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Cartxis\Product\Models\Category;

class MigrateCategoriesCommand extends Command
{
    protected $signature = 'bagisto:migrate:categories {--dry-run : Preview migration without making changes}';
    
    protected $description = 'Migrate Bagisto categories to Cartxis';
    
    private array $errors = [];
    
    public function handle(): int
    {
        $config = config('bagisto-migration');
        
        // Get categories from Bagisto
        $categories = DB::connection($config['connection'])
            ->table('categories as c')
            ->leftJoin('category_translations as ct', function($join) use ($config) {
                $join->on('c.id', '=', 'ct.category_id')
                     ->where('ct.locale', '=', $config['defaults']['locale_code']);
            })
            ->select('c.id', 'c.parent_id', 'c.position', 'c.status',
                    'ct.name', 'ct.slug', 'ct.description', 'ct.meta_title', 'ct.meta_description')
            ->get();
        
        if ($categories->isEmpty()) {
            $this->warn('No categories found to migrate.');
            return self::SUCCESS;
        }
        
        $this->info("Found {$categories->count()} categories to migrate.");
        
        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Name', 'Slug', 'Parent ID', 'Status'],
                $categories->map(fn($cat) => [
                    $cat->id,
                    $cat->name ?? 'N/A',
                    $cat->slug ?? 'N/A',
                    $cat->parent_id ?: '-',
                    $cat->status ? 'Active' : 'Inactive',
                ])->toArray()
            );
            return self::SUCCESS;
        }
        
        $this->newLine();
        $bar = $this->output->createProgressBar($categories->count());
        $bar->start();
        
        foreach ($categories as $bagistoCategory) {
            try {
                Category::updateOrCreate(
                    ['slug' => $bagistoCategory->slug],
                    [
                        'name' => $bagistoCategory->name,
                        'description' => $bagistoCategory->description,
                        'parent_id' => $bagistoCategory->parent_id ?: null,
                        'status' => $bagistoCategory->status ? 'enabled' : 'disabled',
                        'sort_order' => $bagistoCategory->position,
                        'meta' => [
                            'bagisto_id' => $bagistoCategory->id,
                            'meta_title' => $bagistoCategory->meta_title,
                            'meta_description' => $bagistoCategory->meta_description,
                        ],
                    ]
                );
            } catch (\Exception $e) {
                $this->errors[] = "Category '{$bagistoCategory->name}': {$e->getMessage()}";
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
