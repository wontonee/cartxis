<?php

declare(strict_types=1);

namespace Cartxis\System\Console\Commands\Migration\WooCommerce;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Cartxis\Shop\Models\Order;
use Cartxis\Shop\Models\OrderItem;

class MigrateOrdersCommand extends Command
{
    protected $signature = 'wc:migrate:orders {--dry-run : Preview migration without making changes} {--batch=500 : Number of records per batch}';
    
    protected $description = 'Migrate WooCommerce orders to Cartxis';
    
    private array $errors = [];
    private bool $hposEnabled;
    
    public function handle(): int
    {
        $config = config('woocommerce-migration');
        $this->hposEnabled = $config['hpos_enabled'];
        $prefix = $config['database']['prefix'];
        
        // Check which table structure to use
        if ($this->hposEnabled) {
            $orders = $this->getHposOrders($config, $prefix);
        } else {
            $orders = $this->getLegacyOrders($config, $prefix);
        }
        
        if ($orders->isEmpty()) {
            $this->warn('No orders found to migrate.');
            return self::SUCCESS;
        }
        
        $this->info("Found {$orders->count()} orders to migrate.");
        
        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Order Number', 'Customer Email', 'Total', 'Status', 'Date'],
                $orders->take(10)->map(fn($o) => [
                    $o->id,
                    $o->order_number ?? $o->id,
                    $o->customer_email,
                    $o->total,
                    $o->status,
                    $o->date_created,
                ])->toArray()
            );
            $this->info("Showing 10 of {$orders->count()} orders...");
            return self::SUCCESS;
        }
        
        $this->newLine();
        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();
        
        foreach ($orders as $wcOrder) {
            try {
                $this->migrateOrder($config, $prefix, $wcOrder);
            } catch (\Exception $e) {
                $this->errors[] = "Order #{$wcOrder->id}: {$e->getMessage()}";
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        if (empty($this->errors)) {
            $this->info("✓ Successfully migrated {$orders->count()} orders.");
        } else {
            $this->warn("Migrated with " . count($this->errors) . " errors:");
            foreach (array_slice($this->errors, 0, 10) as $error) {
                $this->error("  - {$error}");
            }
        }
        
        return self::SUCCESS;
    }
    
    private function getHposOrders(array $config, string $prefix)
    {
        return DB::connection($config['connection'])
            ->table($prefix . 'wc_orders')
            ->select('id', 'status', 'currency', 'total_amount as total', 
                    'customer_id', 'billing_email as customer_email', 
                    'date_created_gmt as date_created')
            ->get();
    }
    
    private function getLegacyOrders(array $config, string $prefix)
    {
        return DB::connection($config['connection'])
            ->table($prefix . 'posts as p')
            ->leftJoin($prefix . 'postmeta as pm_total', function($join) {
                $join->on('p.ID', '=', 'pm_total.post_id')
                     ->where('pm_total.meta_key', '=', '_order_total');
            })
            ->leftJoin($prefix . 'postmeta as pm_email', function($join) {
                $join->on('p.ID', '=', 'pm_email.post_id')
                     ->where('pm_email.meta_key', '=', '_billing_email');
            })
            ->where('p.post_type', 'shop_order')
            ->select('p.ID as id', 'p.post_status as status', 
                    'pm_total.meta_value as total',
                    'pm_email.meta_value as customer_email',
                    'p.post_date as date_created')
            ->get();
    }
    
    private function migrateOrder(array $config, string $prefix, object $wcOrder): void
    {
        // Map status
        $statusMapping = $config['mappings']['order_status'];
        $status = $statusMapping[$wcOrder->status] ?? 'pending';
        
        // Create order
        $order = Order::updateOrCreate(
            ['order_number' => "WC-{$wcOrder->id}"],
            [
                'customer_email' => $wcOrder->customer_email,
                'status' => $status,
                'subtotal' => (float) $wcOrder->total,
                'total' => (float) $wcOrder->total,
                'currency' => $wcOrder->currency ?? 'USD',
                'meta' => [
                    'woocommerce_id' => $wcOrder->id,
                ],
                'created_at' => $wcOrder->date_created,
            ]
        );
        
        // Migrate order items
        $this->migrateOrderItems($config, $prefix, $wcOrder->id, $order->id);
    }
    
    private function migrateOrderItems(array $config, string $prefix, int $wcOrderId, int $vortexOrderId): void
    {
        if ($this->hposEnabled) {
            $items = DB::connection($config['connection'])
                ->table($prefix . 'wc_order_items')
                ->where('order_id', $wcOrderId)
                ->where('order_item_type', 'line_item')
                ->get();
        } else {
            $items = DB::connection($config['connection'])
                ->table($prefix . 'woocommerce_order_items')
                ->where('order_id', $wcOrderId)
                ->where('order_item_type', 'line_item')
                ->get();
        }
        
        foreach ($items as $item) {
            $itemMeta = $this->getOrderItemMeta($config, $prefix, $item->order_item_id);
            
            OrderItem::updateOrCreate(
                [
                    'order_id' => $vortexOrderId,
                    'product_id' => $itemMeta['product_id'] ?? null,
                ],
                [
                    'product_name' => $item->order_item_name,
                    'quantity' => (int) ($itemMeta['qty'] ?? 1),
                    'price' => (float) ($itemMeta['line_total'] ?? 0),
                    'total' => (float) ($itemMeta['line_total'] ?? 0),
                ]
            );
        }
    }
    
    private function getOrderItemMeta(array $config, string $prefix, int $itemId): array
    {
        $table = $this->hposEnabled 
            ? $prefix . 'wc_order_itemmeta'
            : $prefix . 'woocommerce_order_itemmeta';
        
        $metaRows = DB::connection($config['connection'])
            ->table($table)
            ->where('order_item_id', $itemId)
            ->whereIn('meta_key', ['_product_id', '_qty', '_line_total', '_line_subtotal'])
            ->get();
        
        $meta = [];
        foreach ($metaRows as $row) {
            $key = ltrim($row->meta_key, '_');
            $meta[$key] = $row->meta_value;
        }
        
        return $meta;
    }
}
