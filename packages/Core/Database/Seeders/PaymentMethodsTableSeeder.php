<?php

namespace Vortex\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_methods')->insert([
            // Cash on Delivery
            [
                'code' => 'cod',
                'name' => 'Cash on Delivery',
                'description' => 'Pay with cash when your order is delivered',
                'type' => 'cod',
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
                'instructions' => 'Please keep exact change ready. Our delivery person will collect payment upon delivery.',
                'configuration' => json_encode([
                    'min_order_amount' => 0,
                    'max_order_amount' => null,
                    'handling_fee' => 0,
                    'handling_fee_type' => 'flat',
                    'allowed_countries' => ['*'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Bank Transfer
            [
                'code' => 'bank_transfer',
                'name' => 'Bank Transfer',
                'description' => 'Transfer payment directly to our bank account',
                'type' => 'bank_transfer',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
                'instructions' => 'Please transfer the order amount to our bank account and use the reference format provided during checkout.',
                'configuration' => json_encode([
                    'account_name' => 'Vortex Shop Inc.',
                    'bank_name' => '',
                    'account_number' => '',
                    'swift_code' => '',
                    'iban' => '',
                    'ifsc_code' => '',
                    'branch_address' => '',
                    'reference_format' => 'ORDER-{order_id}',
                    'verification_days' => 3,
                    'auto_confirm' => false,
                    'allowed_countries' => ['*'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Stripe
            [
                'code' => 'stripe',
                'name' => 'Stripe',
                'description' => 'Pay securely using Stripe with credit card, Apple Pay, Google Pay, and more',
                'type' => 'stripe',
                'is_active' => false,
                'is_default' => false,
                'sort_order' => 3,
                'instructions' => 'You will be redirected to Stripe to complete your payment securely.',
                'configuration' => json_encode([
                    'public_key' => env('STRIPE_PUBLIC_KEY', ''),
                    'enable_3d_secure' => true,
                    'save_payment_method' => true,
                    'payment_methods' => [
                        'card' => true,
                        'apple_pay' => true,
                        'google_pay' => true,
                        'ideal' => false,
                        'bancontact' => false,
                        'eps' => false,
                        'giropay' => false,
                        'klarna' => false,
                        'p24' => false,
                        'alipay' => false,
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
