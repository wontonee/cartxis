<?php

namespace Vortex\Sales\Database\Seeders;

use Illuminate\Database\Seeder;
use Vortex\Core\Models\EmailTemplate;

class ShipmentEmailTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Shipment Shipped Template
        EmailTemplate::updateOrCreate(
            ['code' => 'shipment_shipped'],
            [
                'name' => 'Shipment Shipped',
                'description' => 'Sent when a shipment is marked as shipped',
                'category' => 'shipment',
                'subject' => 'Your Order #{order_number} Has Shipped!',
                'from_name' => '{store_name}',
                'html_content' => '<h2>Your Order Has Shipped! 📦</h2>
<p>Hi {customer_name},</p>
<p>Great news! Your order #{order_number} is on its way!</p>
<p><strong>Tracking Number:</strong> {tracking_number}</p>
<p><strong>Shipping Address:</strong><br>{shipping_address}</p>
<p>You can track your shipment using the tracking number above.</p>
<p>Thank you for your order!</p>
<p>{store_name}</p>',
                'plain_text_content' => "Hi {customer_name},\n\nYour order #{order_number} has been shipped!\n\nTracking Number: {tracking_number}\nShipping Address: {shipping_address}\n\nThank you for your order!\n\n{store_name}",
                'variables' => [
                    'customer_name',
                    'order_number',
                    'shipment_number',
                    'tracking_number',
                    'shipping_address',
                    'store_name',
                ],
                'is_active' => true,
                'is_system' => false,
                'locale' => 'en',
            ]
        );

        // Shipment Delivered Template
        EmailTemplate::updateOrCreate(
            ['code' => 'shipment_delivered'],
            [
                'name' => 'Shipment Delivered',
                'description' => 'Sent when a shipment is marked as delivered',
                'category' => 'shipment',
                'subject' => 'Your Order #{order_number} Has Been Delivered!',
                'from_name' => '{store_name}',
                'html_content' => '<h2>Your Order Has Been Delivered! ✅</h2>
<p>Hi {customer_name},</p>
<p>Great news! Your order #{order_number} has been delivered!</p>
<p><strong>Delivered on:</strong> {delivered_at}</p>
<p>We hope you enjoy your purchase! If you have any questions or concerns, please don\'t hesitate to contact us.</p>
<p>Thank you for shopping with us!</p>
<p>{store_name}</p>',
                'plain_text_content' => "Hi {customer_name},\n\nGreat news! Your order #{order_number} has been delivered!\n\nDelivered on: {delivered_at}\n\nThank you for shopping with us!\n\n{store_name}",
                'variables' => [
                    'customer_name',
                    'order_number',
                    'shipment_number',
                    'delivered_at',
                    'store_name',
                ],
                'is_active' => true,
                'is_system' => false,
                'locale' => 'en',
            ]
        );

        $this->command->info('Shipment email templates created successfully.');
    }
}
