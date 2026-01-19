<?php

declare(strict_types=1);

namespace Cartxis\System\Console\Commands\Migration\Bagisto;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Cartxis\Shop\Models\Order;
use Cartxis\Shop\Models\OrderItem;

class MigrateOrdersCommand extends Command
{
    protected $signature = 'bagisto:migrate:orders {--dry-run : Preview migration without making changes} {--batch=500 : Number of records per batch}';
    
    protected $description = 'Migrate Bagisto orders to Cartxis';
    
    private array $errors = [];
    
    public function handle(): int
    {
        $config = config('bagisto-migration');
        
        // Get orders from Bagisto
        $orders = DB::connection($config['connection'])
            ->table('orders')
            ->select('id', 'increment_id', 'status', 'customer_id', 'customer_email',
                    'customer_first_name', 'customer_last_name',
                    'base_currency_code', 'base_sub_total', 'base_grand_total',
                    'created_at')
            ->get();
        
        if ($orders->isEmpty()) {
            $this->warn('No orders found to migrate.');
            return self::SUCCESS;
        }
        
        $this->info("Found {$orders->count()} orders to migrate.");
        
        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Order #', 'Customer', 'Total', 'Status', 'Date'],
                $orders->take(10)->map(fn($o) => [
                    $o->id,
                    $o->increment_id,
                    $o->customer_email,
                    $o->base_grand_total,
                    $o->status,
                    $o->created_at,
                ])->toArray()
            );
            $this->info("Showing 10 of {$orders->count()} orders...");
            return self::SUCCESS;
        }
        
        $this->newLine();
        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();
        
        foreach ($orders as $bagistoOrder) {
            try {
                $this->migrateOrder($config, $bagistoOrder);
            } catch (\Exception $e) {
                $this->errors[] = "Order #{$bagistoOrder->increment_id}: {$e->getMessage()}";
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
    
    private function migrateOrder(array $config, object $bagistoOrder): void
    {
        // Map status
        $statusMapping = $config['mappings']['order_status'];
        $status = $statusMapping[$bagistoOrder->status] ?? 'pending';
        
        // Create order
        $order = Order::updateOrCreate(
            ['order_number' => "BGS-{$bagistoOrder->increment_id}"],
            [
                'customer_email' => $bagistoOrder->customer_email,
                'customer_first_name' => $bagistoOrder->customer_first_name,
                'customer_last_name' => $bagistoOrder->customer_last_name,
                'status' => $status,
                'subtotal' => (float) $bagistoOrder->base_sub_total,
                'total' => (float) $bagistoOrder->base_grand_total,
                'currency' => $bagistoOrder->base_currency_code,
                'meta' => [
                    'bagisto_id' => $bagistoOrder->id,
                    'bagisto_customer_id' => $bagistoOrder->customer_id,
                ],
                'created_at' => $bagistoOrder->created_at,
            ]
        );
        
        // Migrate order items
        $this->migrateOrderItems($config, $bagistoOrder->id, $order->id);
    }
    
    private function migrateOrderItems(array $config, int $bagistoOrderId, int $vortexOrderId): void
    {
        $items = DB::connection($config['connection'])
            ->table('order_items')
            ->where('order_id', $bagistoOrderId)
            ->select('id', 'product_id', 'sku', 'name', 'qty_ordered', 'price', 'total')
            ->get();
        
        foreach ($items as $item) {
            OrderItem::updateOrCreate(
                [
                    'order_id' => $vortexOrderId,
                    'product_sku' => $item->sku,
                ],
                [
                    'product_id' => $item->product_id,
                    'product_name' => $item->name,
                    'quantity' => (int) $item->qty_ordered,
                    'price' => (float) $item->price,
                    'total' => (float) $item->total,
                    'meta' => [
                        'bagisto_order_item_id' => $item->id,
                    ],
                ]
            );
        }
    }
}
