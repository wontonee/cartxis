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
        $shippedHtml = file_get_contents(base_path('packages/Vortex/Sales/src/resources/views/emails/shipment-shipped.blade.php'));
        $deliveredHtml = file_get_contents(base_path('packages/Vortex/Sales/src/resources/views/emails/shipment-delivered.blade.php'));

        // Shipment Shipped Template
        EmailTemplate::updateOrCreate(
            ['code' => 'shipment_shipped'],
            [
                'name' => 'Shipment Shipped',
                'description' => 'Sent when a shipment is marked as shipped',
                'category' => 'shipment',
                'subject' => 'Your order has been shipped - {shipment_number}',
                'from_name' => '{store_name}',
                'html_content' => $shippedHtml,
                'plain_text_content' => "Hi {customer_name},\n\nYour order {order_number} has been shipped!\n\nShipment Details:\n- Shipment Number: {shipment_number}\n- Carrier: {carrier}\n- Tracking Number: {tracking_number}\n- Tracking URL: {tracking_url}\n- Shipped Date: {shipped_at}\n\nShipped Items ({items_count} items):\n{items}\n\nYou can track your package using the tracking number above.\n\nThank you for shopping with us!\n\n{store_name}\n{store_url}",
                'variables' => [
                    'shipment_number',
                    'order_number',
                    'customer_name',
                    'customer_email',
                    'carrier',
                    'tracking_number',
                    'tracking_url',
                    'shipped_at',
                    'items_count',
                    'items',
                    'store_name',
                    'store_url'
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
                'subject' => 'Your order has been delivered - {shipment_number}',
                'from_name' => '{store_name}',
                'html_content' => $deliveredHtml,
                'plain_text_content' => "Hi {customer_name},\n\nGreat news! Your order {order_number} has been delivered!\n\nDelivery Details:\n- Shipment Number: {shipment_number}\n- Delivered Date: {delivered_at}\n\nDelivered Items ({items_count} items):\n{items}\n\nWe hope you enjoy your purchase! If you have any questions or concerns, please don't hesitate to contact us.\n\nWould you like to leave a review? Your feedback helps us improve!\n\nThank you for shopping with us!\n\n{store_name}\n{store_url}",
                'variables' => [
                    'shipment_number',
                    'order_number',
                    'customer_name',
                    'customer_email',
                    'delivered_at',
                    'items_count',
                    'items',
                    'store_name',
                    'store_url'
                ],
                'is_active' => true,
                'is_system' => false,
                'locale' => 'en',
            ]
        );

        $this->command->info('Shipment email templates created successfully.');
    }
}
