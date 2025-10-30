<?php

namespace Vortex\Sales\Mail;

use Vortex\Sales\Models\Invoice;
use Vortex\Core\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    public string $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, string $pdfPath)
    {
        $this->invoice = $invoice;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $template = EmailTemplate::findByCode('invoice_sent');
        
        if ($template) {
            // Use template system
            $data = $this->getTemplateData();
            $rendered = $template->render($data);
            
            // Get email configuration
            $emailConfig = \Vortex\Core\Models\EmailConfiguration::where('is_active', true)->first();
            
            $mail = $this->subject($rendered['subject'])
                ->attach($this->pdfPath, [
                    'as' => $this->invoice->invoice_number . '.pdf',
                    'mime' => 'application/pdf',
                ]);
                
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
        return $this->subject('Invoice ' . $this->invoice->invoice_number . ' from Vortex')
            ->view('sales::emails.invoice')
            ->with([
                'invoice' => $this->invoice,
                'order' => $this->invoice->order,
            ])
            ->attach($this->pdfPath, [
                'as' => $this->invoice->invoice_number . '.pdf',
                'mime' => 'application/pdf',
            ]);
    }

    /**
     * Get template data for variable replacement
     */
    private function getTemplateData(): array
    {
        $order = $this->invoice->order;
        
        return [
            'customer_name' => $order->customer_name ?? 'Valued Customer',
            'invoice_number' => $this->invoice->invoice_number,
            'order_number' => $order->order_number,
            'issue_date' => $this->invoice->issue_date->format('F d, Y'),
            'due_date' => $this->invoice->due_date->format('F d, Y'),
            'status' => strtoupper($this->invoice->status),
            'total' => '₹' . number_format($this->invoice->total, 2),
            'store_name' => config('app.name', 'Vortex'),
            'store_url' => config('app.url'),
            'year' => date('Y'),
        ];
    }
}
