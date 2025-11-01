<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Vortex\Sales\Models\Transaction;
use Vortex\Shop\Models\Order;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Get some existing orders
        $orders = Order::take(5)->get();

        if ($orders->isEmpty()) {
            $this->command->warn('No orders found. Please run OrderSeeder first.');
            return;
        }

        $gateways = ['stripe', 'paypal', 'razorpay', 'cod', 'bank_transfer'];
        $types = ['payment', 'refund', 'authorization', 'capture'];
        $statuses = ['completed', 'pending', 'failed', 'cancelled'];

        foreach ($orders as $order) {
            // Create a payment transaction for each order
            Transaction::create([
                'order_id' => $order->id,
                'transaction_number' => Transaction::generateTransactionNumber(),
                'type' => 'payment',
                'payment_method' => $order->payment_method ?? 'stripe',
                'gateway' => $gateways[array_rand($gateways)],
                'gateway_transaction_id' => 'TXN_' . strtoupper(uniqid()),
                'amount' => $order->total,
                'status' => 'completed',
                'response_data' => [
                    'gateway_response' => [
                        'status' => 'success',
                        'code' => '200',
                        'message' => 'Payment processed successfully',
                    ],
                    'payment_details' => [
                        'card_last4' => '4242',
                        'card_brand' => 'visa',
                    ],
                ],
                'notes' => 'Payment processed successfully',
                'processed_at' => now()->subDays(rand(1, 10)),
                'created_at' => now()->subDays(rand(1, 10)),
            ]);

            // Randomly create additional transactions
            if (rand(0, 1)) {
                // Add a refund transaction for some orders
                Transaction::create([
                    'order_id' => $order->id,
                    'transaction_number' => Transaction::generateTransactionNumber(),
                    'type' => 'refund',
                    'payment_method' => $order->payment_method ?? 'stripe',
                    'gateway' => $gateways[array_rand($gateways)],
                    'gateway_transaction_id' => 'REF_' . strtoupper(uniqid()),
                    'amount' => $order->total * 0.5, // Partial refund
                    'status' => 'completed',
                    'response_data' => [
                        'gateway_response' => [
                            'status' => 'success',
                            'code' => '200',
                            'message' => 'Refund processed successfully',
                        ],
                    ],
                    'notes' => 'Partial refund issued',
                    'processed_at' => now()->subDays(rand(1, 5)),
                    'created_at' => now()->subDays(rand(1, 5)),
                ]);
            }
        }

        // Add some pending transactions
        if ($orders->count() > 0) {
            $order = $orders->random();
            Transaction::create([
                'order_id' => $order->id,
                'transaction_number' => Transaction::generateTransactionNumber(),
                'type' => 'payment',
                'payment_method' => 'stripe',
                'gateway' => 'stripe',
                'gateway_transaction_id' => null,
                'amount' => $order->total,
                'status' => 'pending',
                'notes' => 'Payment pending confirmation',
                'created_at' => now(),
            ]);
        }

        // Add some failed transactions
        if ($orders->count() > 0) {
            $order = $orders->random();
            Transaction::create([
                'order_id' => $order->id,
                'transaction_number' => Transaction::generateTransactionNumber(),
                'type' => 'payment',
                'payment_method' => 'stripe',
                'gateway' => 'stripe',
                'gateway_transaction_id' => 'FAIL_' . strtoupper(uniqid()),
                'amount' => $order->total,
                'status' => 'failed',
                'response_data' => [
                    'gateway_response' => [
                        'status' => 'failed',
                        'code' => '402',
                        'message' => 'Insufficient funds',
                    ],
                ],
                'notes' => 'Payment failed: Insufficient funds',
                'created_at' => now()->subHours(2),
            ]);
        }

        $this->command->info('Transaction seeder completed successfully!');
    }
}
