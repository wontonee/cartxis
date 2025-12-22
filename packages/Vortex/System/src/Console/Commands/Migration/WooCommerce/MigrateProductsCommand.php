<?php

declare(strict_types=1);

namespace Vortex\System\Console\Commands\Migration\WooCommerce;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Vortex\Product\Models\Product;

class MigrateProductsCommand extends Command
{
    protected $signature = 'wc:migrate:products {--dry-run : Preview migration without making changes} {--batch=500 : Number of records per batch}';
    
    protected $description = 'Migrate WooCommerce products to Vortex';
    
    private array $errors = [];
    
    public function handle(): int
    {
        $config = config('woocommerce-migration');
        $prefix = $config['database']['prefix'];
        $batchSize = (int) $this->option('batch');
        
        // Get products from WooCommerce
        $products = DB::connection($config['connection'])
            ->table($prefix . 'posts')
            ->where('post_type', 'product')
            ->whereIn('post_status', ['publish', 'draft', 'private'])
            ->select('ID', 'post_title', 'post_name', 'post_content', 'post_excerpt', 'post_status', 'post_date')
            ->get();
        
        if ($products->isEmpty()) {
            $this->warn('No products found to migrate.');
            return self::SUCCESS;
        }
        
        $this->info("Found {$products->count()} products to migrate.");
        
        if ($this->option('dry-run')) {
            $preview = $products->take(10);
            $this->table(
                ['ID', 'Name', 'Slug', 'Status', 'Created'],
                $preview->map(fn($p) => [
                    $p->ID,
                    $p->post_title,
                    $p->post_name,
                    $p->post_status,
                    $p->post_date,
                ])->toArray()
            );
            $this->info("Showing 10 of {$products->count()} products...");
            return self::SUCCESS;
        }
        
        $this->newLine();
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();
        
        foreach ($products as $wcProduct) {
            try {
                // Get product meta
                $meta = $this->getProductMeta($config, $prefix, $wcProduct->ID);
                
                // Map status
                $status = $config['mappings']['product_status'][$wcProduct->post_status] ?? 'draft';
                
                Product::updateOrCreate(
                    ['sku' => $meta['sku'] ?? "WC-{$wcProduct->ID}"],
                    [
                        'name' => $wcProduct->post_title,
                        'slug' => $wcProduct->post_name,
                        'description' => $wcProduct->post_content,
                        'short_description' => $wcProduct->post_excerpt,
                        'status' => $status,
                        'price' => (float) ($meta['price'] ?? 0),
                        'regular_price' => (float) ($meta['regular_price'] ?? 0),
                        'sale_price' => isset($meta['sale_price']) ? (float) $meta['sale_price'] : null,
                        'quantity' => (int) ($meta['stock_quantity'] ?? 0),
                        'manage_stock' => ($meta['manage_stock'] ?? 'no') === 'yes',
                        'type' => $meta['product_type'] ?? 'simple',
                        'meta' => [
                            'woocommerce_id' => $wcProduct->ID,
                            'weight' => $meta['weight'] ?? null,
                            'dimensions' => [
                                'length' => $meta['length'] ?? null,
                                'width' => $meta['width'] ?? null,
                                'height' => $meta['height'] ?? null,
                            ],
                        ],
                        'created_at' => $wcProduct->post_date,
                    ]
                );
            } catch (\Exception $e) {
                $this->errors[] = "Product '{$wcProduct->post_title}': {$e->getMessage()}";
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
    
    private function getProductMeta(array $config, string $prefix, int $productId): array
    {
        $metaRows = DB::connection($config['connection'])
            ->table($prefix . 'postmeta')
            ->where('post_id', $productId)
            ->whereIn('meta_key', [
                '_sku', '_price', '_regular_price', '_sale_price',
                '_stock_quantity', '_manage_stock', '_stock_status',
                '_weight', '_length', '_width', '_height',
                '_product_type',
            ])
            ->get();
        
        $meta = [];
        foreach ($metaRows as $row) {
            $key = ltrim($row->meta_key, '_');
            $meta[$key] = $row->meta_value;
        }
        
        return $meta;
    }
}
