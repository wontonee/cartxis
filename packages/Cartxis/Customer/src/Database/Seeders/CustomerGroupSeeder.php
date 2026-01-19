<?php

declare(strict_types=1);

namespace Cartxis\Customer\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\Customer\Models\CustomerGroup;

class CustomerGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'General',
                'code' => 'general',
                'description' => 'Default customer group for all new customers',
                'color' => '#3B82F6',
                'discount_percentage' => 0.00,
                'order' => 1,
                'is_default' => true,
                'auto_assignment_rules' => null,
                'status' => true,
            ],
            [
                'name' => 'Wholesale',
                'code' => 'wholesale',
                'description' => 'Wholesale customers with bulk purchase discounts',
                'color' => '#10B981',
                'discount_percentage' => 15.00,
                'order' => 2,
                'is_default' => false,
                'auto_assignment_rules' => [
                    'min_orders' => 5,
                    'min_amount' => 5000.00,
                ],
                'status' => true,
            ],
            [
                'name' => 'VIP',
                'code' => 'vip',
                'description' => 'VIP customers with premium benefits',
                'color' => '#F59E0B',
                'discount_percentage' => 20.00,
                'order' => 3,
                'is_default' => false,
                'auto_assignment_rules' => [
                    'min_orders' => 10,
                    'min_amount' => 10000.00,
                ],
                'status' => true,
            ],
            [
                'name' => 'Retail',
                'code' => 'retail',
                'description' => 'Regular retail customers',
                'color' => '#8B5CF6',
                'discount_percentage' => 5.00,
                'order' => 4,
                'is_default' => false,
                'auto_assignment_rules' => null,
                'status' => true,
            ],
        ];

        foreach ($groups as $group) {
            CustomerGroup::updateOrCreate(
                ['code' => $group['code']],
                $group
            );
        }

        $this->command->info('Customer groups seeded successfully!');
    }
}
