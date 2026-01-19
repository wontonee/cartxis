<?php

namespace Cartxis\Sales\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\Core\Models\EmailTemplate;

class CreditMemoEmailTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EmailTemplate::updateOrCreate(
            ['code' => 'credit_memo_created'],
            [
                'name' => 'Credit Memo Created',
                'category' => 'credit_memo',
                'subject' => 'Refund Processed - Credit Memo {credit_memo_number}',
                'html_content' => $this->getCreditMemoHtml(),
                'plain_text_content' => $this->getCreditMemoPlainText(),
                'variables' => [
                    'customer_name',
                    'credit_memo_number',
                    'order_number',
                    'refund_amount',
                    'refund_method',
                    'issue_date',
                    'store_name',
                    'store_url',
                    'year',
                ],
                'is_active' => true,
            ]
        );
    }

    /**
     * Get HTML content for credit memo email
     */
    protected function getCreditMemoHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #10B981;">Refund Processed ✓</h2>
        
        <p>Hi {customer_name},</p>
        
        <p>We have processed a refund for your order. Please find the details below:</p>
        
        <div style="background-color: #F3F4F6; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Credit Memo Number:</strong> {credit_memo_number}</p>
            <p style="margin: 5px 0;"><strong>Order Number:</strong> {order_number}</p>
            <p style="margin: 5px 0;"><strong>Refund Amount:</strong> {refund_amount}</p>
            <p style="margin: 5px 0;"><strong>Refund Method:</strong> {refund_method}</p>
            <p style="margin: 5px 0;"><strong>Issue Date:</strong> {issue_date}</p>
        </div>
        
        <div style="background-color: #FEF3C7; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>⏱️ When will I receive my refund?</strong></p>
            <p style="margin: 5px 0;">Depending on your payment method, it may take 3-10 business days for the refund to appear in your account.</p>
        </div>
        
        <p>If you have any questions about this refund, please don't hesitate to contact our customer support team.</p>
        
        <p>
            <a href="{store_url}" style="display: inline-block; background-color: #10B981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Visit Our Store</a>
        </p>
        
        <hr style="border: none; border-top: 1px solid #E5E7EB; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #6B7280;">
            {store_name}<br>
            <a href="{store_url}" style="color: #10B981;">{store_url}</a><br>
            &copy; {year} {store_name}. All rights reserved.
        </p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get plain text content for credit memo email
     */
    protected function getCreditMemoPlainText(): string
    {
        return <<<'TEXT'
Refund Processed

Hi {customer_name},

We have processed a refund for your order. Please find the details below:

Credit Memo Number: {credit_memo_number}
Order Number: {order_number}
Refund Amount: {refund_amount}
Refund Method: {refund_method}
Issue Date: {issue_date}

When will I receive my refund?
Depending on your payment method, it may take 3-10 business days for the refund to appear in your account.

If you have any questions about this refund, please don't hesitate to contact our customer support team.

Visit Our Store: {store_url}

---
{store_name}
{store_url}
© {year} {store_name}. All rights reserved.
TEXT;
    }
}
