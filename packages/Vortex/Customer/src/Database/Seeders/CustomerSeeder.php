<?php

declare(strict_types=1);

namespace Vortex\Customer\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Vortex\Customer\Models\Customer;
use Vortex\Customer\Models\CustomerAddress;
use Vortex\Customer\Models\CustomerGroup;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get customer groups
        $generalGroup = CustomerGroup::where('code', 'general')->first();
        $wholesaleGroup = CustomerGroup::where('code', 'wholesale')->first();
        $vipGroup = CustomerGroup::where('code', 'vip')->first();

        if (!$generalGroup) {
            $this->command->error('Please run CustomerGroupSeeder first!');
            return;
        }

        // Create sample customers
        $customers = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567890',
                'date_of_birth' => '1985-06-15',
                'gender' => 'male',
                'customer_group_id' => $generalGroup->id,
                'is_active' => true,
                'is_verified' => true,
                'newsletter_subscribed' => true,
                'total_orders' => 5,
                'total_spent' => 1250.00,
                'average_order_value' => 250.00,
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+1234567891',
                'date_of_birth' => '1990-03-22',
                'gender' => 'female',
                'customer_group_id' => $vipGroup ? $vipGroup->id : $generalGroup->id,
                'is_active' => true,
                'is_verified' => true,
                'newsletter_subscribed' => true,
                'total_orders' => 15,
                'total_spent' => 5500.00,
                'average_order_value' => 366.67,
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'email' => 'michael.j@example.com',
                'phone' => '+1234567892',
                'date_of_birth' => '1982-11-08',
                'gender' => 'male',
                'customer_group_id' => $wholesaleGroup ? $wholesaleGroup->id : $generalGroup->id,
                'company_name' => 'Johnson Trading Co.',
                'tax_id' => 'TAX123456789',
                'is_active' => true,
                'is_verified' => true,
                'newsletter_subscribed' => false,
                'total_orders' => 8,
                'total_spent' => 3200.00,
                'average_order_value' => 400.00,
            ],
        ];

        foreach ($customers as $customerData) {
            $customer = Customer::updateOrCreate(
                ['email' => $customerData['email']],
                $customerData
            );

            // Create addresses for each customer
            $this->createAddressesForCustomer($customer);
        }

        $this->command->info('Sample customers seeded successfully!');
    }

    /**
     * Create sample addresses for a customer.
     */
    private function createAddressesForCustomer(Customer $customer): void
    {
        // Shipping address
        CustomerAddress::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'type' => 'shipping',
                'is_default_shipping' => true,
            ],
            [
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'company' => $customer->company_name,
                'address_line_1' => '123 Main Street',
                'address_line_2' => 'Apt 4B',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'US',
                'phone' => $customer->phone,
                'is_default_billing' => false,
            ]
        );

        // Billing address
        CustomerAddress::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'type' => 'billing',
                'is_default_billing' => true,
            ],
            [
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'company' => $customer->company_name,
                'address_line_1' => '456 Business Ave',
                'address_line_2' => 'Suite 200',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10002',
                'country' => 'US',
                'phone' => $customer->phone,
                'is_default_shipping' => false,
            ]
        );
    }
}
