<?php

declare(strict_types=1);

namespace Cartxis\System\Console\Commands\Migration\WooCommerce;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Cartxis\Customer\Models\Customer;

class MigrateCustomersCommand extends Command
{
    protected $signature = 'wc:migrate:customers {--dry-run : Preview migration without making changes} {--batch=500 : Number of records per batch}';
    
    protected $description = 'Migrate WooCommerce customers to Cartxis';
    
    private array $errors = [];
    
    public function handle(): int
    {
        $config = config('woocommerce-migration');
        $prefix = $config['database']['prefix'];
        
        // Get customers (WordPress users with customer role)
        $customers = DB::connection($config['connection'])
            ->table($prefix . 'users as u')
            ->join($prefix . 'usermeta as um', 'u.ID', '=', 'um.user_id')
            ->where('um.meta_key', $prefix . 'capabilities')
            ->where('um.meta_value', 'like', '%customer%')
            ->select('u.ID', 'u.user_login', 'u.user_email', 'u.user_registered', 'u.display_name')
            ->distinct()
            ->get();
        
        if ($customers->isEmpty()) {
            $this->warn('No customers found to migrate.');
            return self::SUCCESS;
        }
        
        $this->info("Found {$customers->count()} customers to migrate.");
        
        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Username', 'Email', 'Name', 'Registered'],
                $customers->take(10)->map(fn($c) => [
                    $c->ID,
                    $c->user_login,
                    $c->user_email,
                    $c->display_name,
                    $c->user_registered,
                ])->toArray()
            );
            $this->info("Showing 10 of {$customers->count()} customers...");
            return self::SUCCESS;
        }
        
        $this->newLine();
        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();
        
        foreach ($customers as $wcCustomer) {
            try {
                // Get customer meta (billing/shipping addresses)
                $meta = $this->getCustomerMeta($config, $prefix, $wcCustomer->ID);
                
                Customer::updateOrCreate(
                    ['email' => $wcCustomer->user_email],
                    [
                        'first_name' => $meta['first_name'] ?? explode(' ', $wcCustomer->display_name)[0] ?? '',
                        'last_name' => $meta['last_name'] ?? '',
                        'phone' => $meta['billing_phone'] ?? null,
                        'password' => Hash::make(bin2hex(random_bytes(16))), // Generate random password
                        'status' => 'enabled',
                        'email_verified_at' => $wcCustomer->user_registered,
                        'meta' => [
                            'woocommerce_id' => $wcCustomer->ID,
                            'billing_address' => [
                                'first_name' => $meta['billing_first_name'] ?? '',
                                'last_name' => $meta['billing_last_name'] ?? '',
                                'company' => $meta['billing_company'] ?? '',
                                'address_1' => $meta['billing_address_1'] ?? '',
                                'address_2' => $meta['billing_address_2'] ?? '',
                                'city' => $meta['billing_city'] ?? '',
                                'state' => $meta['billing_state'] ?? '',
                                'postcode' => $meta['billing_postcode'] ?? '',
                                'country' => $meta['billing_country'] ?? '',
                                'phone' => $meta['billing_phone'] ?? '',
                            ],
                            'shipping_address' => [
                                'first_name' => $meta['shipping_first_name'] ?? '',
                                'last_name' => $meta['shipping_last_name'] ?? '',
                                'company' => $meta['shipping_company'] ?? '',
                                'address_1' => $meta['shipping_address_1'] ?? '',
                                'address_2' => $meta['shipping_address_2'] ?? '',
                                'city' => $meta['shipping_city'] ?? '',
                                'state' => $meta['shipping_state'] ?? '',
                                'postcode' => $meta['shipping_postcode'] ?? '',
                                'country' => $meta['shipping_country'] ?? '',
                            ],
                        ],
                        'created_at' => $wcCustomer->user_registered,
                    ]
                );
            } catch (\Exception $e) {
                $this->errors[] = "Customer '{$wcCustomer->user_email}': {$e->getMessage()}";
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        if (empty($this->errors)) {
            $this->info("✓ Successfully migrated {$customers->count()} customers.");
            $this->warn("Note: Customer passwords were reset. Users will need to use 'Forgot Password' to set new passwords.");
        } else {
            $this->warn("Migrated with " . count($this->errors) . " errors:");
            foreach (array_slice($this->errors, 0, 10) as $error) {
                $this->error("  - {$error}");
            }
        }
        
        return self::SUCCESS;
    }
    
    private function getCustomerMeta(array $config, string $prefix, int $userId): array
    {
        $metaRows = DB::connection($config['connection'])
            ->table($prefix . 'usermeta')
            ->where('user_id', $userId)
            ->whereIn('meta_key', [
                'first_name', 'last_name',
                'billing_first_name', 'billing_last_name', 'billing_company',
                'billing_address_1', 'billing_address_2', 'billing_city',
                'billing_state', 'billing_postcode', 'billing_country', 'billing_phone',
                'shipping_first_name', 'shipping_last_name', 'shipping_company',
                'shipping_address_1', 'shipping_address_2', 'shipping_city',
                'shipping_state', 'shipping_postcode', 'shipping_country',
            ])
            ->get();
        
        $meta = [];
        foreach ($metaRows as $row) {
            $meta[$row->meta_key] = $row->meta_value;
        }
        
        return $meta;
    }
}
