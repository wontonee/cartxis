<?php

declare(strict_types=1);

namespace Vortex\System\Console\Commands\Migration\Bagisto;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Vortex\Product\Models\Product;

class MigrateProductsCommand extends Command
{
    protected $signature = 'bagisto:migrate:products {--dry-run : Preview migration without making changes} {--batch=500 : Number of records per batch}';
    
    protected $description = 'Migrate Bagisto products to Vortex';
    
    private array $errors = [];
    
    public function handle(): int
    {
        $config = config('bagisto-migration');
        $locale = $config['defaults']['locale_code'];
        
        // Get products from Bagisto with flat table
        $products = DB::connection($config['connection'])
            ->table('product_flat')
            ->where('locale', $locale)
            ->select('id', 'sku', 'type', 'name', 'short_description', 'description',
                    'price', 'special_price', 'status', 'created_at')
            ->get();
        
        if ($products->isEmpty()) {
            $this->warn('No products found to migrate.');
            return self::SUCCESS;
        }
        
        $this->info("Found {$products->count()} products to migrate.");
        
        if ($this->option('dry-run')) {
            $preview = $products->take(10);
            $this->table(
                ['ID', 'SKU', 'Name', 'Type', 'Price', 'Status'],
                $preview->map(fn($p) => [
                    $p->id,
                    $p->sku,
                    $p->name,
                    $p->type,
                    $p->price,
                    $p->status ? 'Active' : 'Inactive',
                ])->toArray()
            );
            $this->info("Showing 10 of {$products->count()} products...");
            return self::SUCCESS;
        }
        
        $this->newLine();
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();
        
        foreach ($products as $bagistoProduct) {
            try {
                // Get inventory
                $inventory = DB::connection($config['connection'])
                    ->table('product_inventories')
                    ->where('product_id', $bagistoProduct->id)
                    ->first();
                
                // Map product type
                $typeMapping = $config['mappings']['product_type'];
                $type = $typeMapping[$bagistoProduct->type] ?? 'simple';
                
                Product::updateOrCreate(
                    ['sku' => $bagistoProduct->sku],
                    [
                        'name' => $bagistoProduct->name,
                        'slug' => \Illuminate\Support\Str::slug($bagistoProduct->name),
                        'description' => $bagistoProduct->description,
                        'short_description' => $bagistoProduct->short_description,
                        'status' => $bagistoProduct->status ? 'enabled' : 'disabled',
                        'price' => (float) $bagistoProduct->price,
                        'regular_price' => (float) $bagistoProduct->price,
                        'sale_price' => $bagistoProduct->special_price ? (float) $bagistoProduct->special_price : null,
                        'quantity' => $inventory ? (int) $inventory->qty : 0,
                        'manage_stock' => $inventory ? true : false,
                        'type' => $type,
                        'meta' => [
                            'bagisto_id' => $bagistoProduct->id,
                            'bagisto_type' => $bagistoProduct->type,
                        ],
                        'created_at' => $bagistoProduct->created_at,
                    ]
                );
            } catch (\Exception $e) {
                $this->errors[] = "Product '{$bagistoProduct->name}': {$e->getMessage()}";
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        if (empty($this->errors)) {
            $this->info("✓ Successfully migrated {$products->count()} products.");
        } else {
            $this->warn("Migrated with " . count($this->errors) . " errors:");
            foreach (array_slice($this->errors, 0, 10) as $error) {
                $this->error("  - {$error}");
            }
            if (count($this->errors) > 10) {
                $this->warn("  ... and " . (count($this->errors) - 10) . " more errors.");
            }
        }
        
        return self::SUCCESS;
    }
}
