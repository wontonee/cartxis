<?php

declare(strict_types=1);

namespace Cartxis\System\Console\Commands\Migration\Bagisto;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Cartxis\Customer\Models\Customer;

class MigrateCustomersCommand extends Command
{
    protected $signature = 'bagisto:migrate:customers {--dry-run : Preview migration without making changes} {--batch=500 : Number of records per batch}';
    
    protected $description = 'Migrate Bagisto customers to Cartxis';
    
    private array $errors = [];
    
    public function handle(): int
    {
        $config = config('bagisto-migration');
        
        // Get customers from Bagisto
        $customers = DB::connection($config['connection'])
            ->table('customers')
            ->select('id', 'first_name', 'last_name', 'email', 'phone', 
                    'password', 'status', 'created_at')
            ->get();
        
        if ($customers->isEmpty()) {
            $this->warn('No customers found to migrate.');
            return self::SUCCESS;
        }
        
        $this->info("Found {$customers->count()} customers to migrate.");
        
        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Name', 'Email', 'Phone', 'Status', 'Created'],
                $customers->take(10)->map(fn($c) => [
                    $c->id,
                    "{$c->first_name} {$c->last_name}",
                    $c->email,
                    $c->phone ?? 'N/A',
                    $c->status ? 'Active' : 'Inactive',
                    $c->created_at,
                ])->toArray()
            );
            $this->info("Showing 10 of {$customers->count()} customers...");
            return self::SUCCESS;
        }
        
        $this->newLine();
        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();
        
        foreach ($customers as $bagistoCustomer) {
            try {
                // Get customer addresses
                $addresses = DB::connection($config['connection'])
                    ->table('customer_addresses')
                    ->where('customer_id', $bagistoCustomer->id)
                    ->get();
                
                $billingAddress = $addresses->firstWhere('default_address', 1) ?? $addresses->first();
                $shippingAddress = $addresses->firstWhere('default_address', 1) ?? $addresses->first();
                
                Customer::updateOrCreate(
                    ['email' => $bagistoCustomer->email],
                    [
                        'first_name' => $bagistoCustomer->first_name,
                        'last_name' => $bagistoCustomer->last_name,
                        'phone' => $bagistoCustomer->phone,
                        'password' => $bagistoCustomer->password, // Keep existing hash
                        'status' => $bagistoCustomer->status ? 'enabled' : 'disabled',
                        'meta' => [
                            'bagisto_id' => $bagistoCustomer->id,
                            'billing_address' => $billingAddress ? [
                                'first_name' => $billingAddress->first_name ?? '',
                                'last_name' => $billingAddress->last_name ?? '',
                                'company_name' => $billingAddress->company_name ?? '',
                                'address1' => $billingAddress->address1 ?? '',
                                'city' => $billingAddress->city ?? '',
                                'state' => $billingAddress->state ?? '',
                                'postcode' => $billingAddress->postcode ?? '',
                                'country' => $billingAddress->country ?? '',
                                'phone' => $billingAddress->phone ?? '',
                            ] : null,
                            'shipping_address' => $shippingAddress ? [
                                'first_name' => $shippingAddress->first_name ?? '',
                                'last_name' => $shippingAddress->last_name ?? '',
                                'company_name' => $shippingAddress->company_name ?? '',
                                'address1' => $shippingAddress->address1 ?? '',
                                'city' => $shippingAddress->city ?? '',
                                'state' => $shippingAddress->state ?? '',
                                'postcode' => $shippingAddress->postcode ?? '',
                                'country' => $shippingAddress->country ?? '',
                                'phone' => $shippingAddress->phone ?? '',
                            ] : null,
                        ],
                        'created_at' => $bagistoCustomer->created_at,
                    ]
                );
            } catch (\Exception $e) {
                $this->errors[] = "Customer '{$bagistoCustomer->email}': {$e->getMessage()}";
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        if (empty($this->errors)) {
            $this->info("✓ Successfully migrated {$customers->count()} customers.");
        } else {
            $this->warn("Migrated with " . count($this->errors) . " errors:");
            foreach (array_slice($this->errors, 0, 10) as $error) {
                $this->error("  - {$error}");
            }
        }
        
        return self::SUCCESS;
    }
}
