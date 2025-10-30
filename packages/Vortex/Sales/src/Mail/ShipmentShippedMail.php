<?php

namespace Vortex\Sales\Mail;

use Vortex\Sales\Models\Shipment;
use Vortex\Core\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShipmentShippedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Shipment $shipment;

    /**
     * Create a new message instance.
     */
    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $template = EmailTemplate::findByCode('shipment_shipped');
        
        if ($template) {
            // Use template system
            $data = $this->getTemplateData();
            $rendered = $template->render($data);
            
            $mail = $this->subject($rendered['subject']);
            
            // Get email configuration
            $emailConfig = \Vortex\Core\Models\EmailConfiguration::where('is_active', true)->first();
                
            // Set from address if specified in template, otherwise use email config
            if ($rendered['from']['email']) {
                $mail->from($rendered['from']['email'], $rendered['from']['name']);
            } elseif ($emailConfig) {
                $mail->from($emailConfig->mail_from_address, $emailConfig->mail_from_name);
            }
            
            // Set reply-to if specified
            if ($rendered['reply_to']) {
                $mail->replyTo($rendered['reply_to']);
            } elseif ($emailConfig && $emailConfig->reply_to_email) {
                $mail->replyTo($emailConfig->reply_to_email);
            }
            
            // Set HTML content
            if ($rendered['html']) {
                $mail->html($rendered['html']);
            }
            
            return $mail;
        }
        
        // Fallback to blade view
        return $this->subject('Your order has been shipped - ' . $this->shipment->shipment_number)
            ->view('sales::emails.shipment-shipped')
            ->with([
                'shipment' => $this->shipment,
                'order' => $this->shipment->order,
            ]);
    }

    /**
     * Get template data for variable replacement
     */
    private function getTemplateData(): array
    {
        return [
            'shipment_number' => $this->shipment->shipment_number,
            'order_number' => $this->shipment->order->order_number,
            'customer_name' => $this->shipment->order->user?->name ?? 'Customer',
            'customer_email' => $this->shipment->order->customer_email,
            'carrier' => $this->shipment->carrier ?? 'N/A',
            'tracking_number' => $this->shipment->tracking_number ?? 'N/A',
            'tracking_url' => $this->shipment->tracking_url ?? '',
            'shipped_at' => $this->shipment->shipped_at?->format('F j, Y, g:i a') ?? now()->format('F j, Y, g:i a'),
            'items_count' => $this->shipment->shipmentItems->count(),
            'items' => $this->shipment->shipmentItems->map(function ($item) {
                return [
                    'name' => $item->orderItem->product->name ?? 'Product',
                    'sku' => $item->orderItem->product->sku ?? 'N/A',
                    'quantity' => $item->quantity,
                ];
            })->toArray(),
        ];
    }
}
