<?php

namespace Cartxis\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\Core\Models\EmailTemplate;

class EmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Order Templates
            [
                'code' => 'order_placed',
                'name' => 'Order Placed',
                'description' => 'Sent when customer places an order',
                'category' => 'order',
                'subject' => 'Order Confirmation #{order_number}',
                'from_name' => '{store_name}',
                'from_email' => null,
                'html_content' => $this->getOrderPlacedTemplate(),
                'plain_text_content' => $this->getOrderPlacedPlainText(),
                'variables' => ['customer_name', 'order_number', 'order_date', 'order_total', 'store_name', 'store_url'],
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'order_shipped',
                'name' => 'Order Shipped',
                'description' => 'Sent when order is shipped',
                'category' => 'order',
                'subject' => 'Your Order #{order_number} Has Shipped!',
                'from_name' => '{store_name}',
                'html_content' => $this->getOrderShippedTemplate(),
                'plain_text_content' => $this->getOrderShippedPlainText(),
                'variables' => ['customer_name', 'order_number', 'tracking_number', 'shipping_address', 'store_name'],
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'order_delivered',
                'name' => 'Order Delivered',
                'description' => 'Sent when order is delivered',
                'category' => 'order',
                'subject' => 'Your Order #{order_number} Has Been Delivered',
                'from_name' => '{store_name}',
                'html_content' => $this->getOrderDeliveredTemplate(),
                'plain_text_content' => $this->getOrderDeliveredPlainText(),
                'variables' => ['customer_name', 'order_number', 'store_name', 'store_url'],
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'order_cancelled',
                'name' => 'Order Cancelled',
                'description' => 'Sent when order is cancelled',
                'category' => 'order',
                'subject' => 'Order #{order_number} Cancelled',
                'from_name' => '{store_name}',
                'html_content' => $this->getOrderCancelledTemplate(),
                'plain_text_content' => $this->getOrderCancelledPlainText(),
                'variables' => ['customer_name', 'order_number', 'order_total', 'store_name'],
                'is_active' => true,
                'is_system' => true,
            ],

            // Account Templates
            [
                'code' => 'customer_welcome',
                'name' => 'Welcome Email',
                'description' => 'Sent when new customer registers',
                'category' => 'account',
                'subject' => 'Welcome to {store_name}!',
                'from_name' => '{store_name}',
                'html_content' => $this->getWelcomeTemplate(),
                'plain_text_content' => $this->getWelcomePlainText(),
                'variables' => ['customer_name', 'customer_email', 'store_name', 'store_url'],
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'password_reset',
                'name' => 'Password Reset',
                'description' => 'Sent when customer requests password reset',
                'category' => 'account',
                'subject' => 'Reset Your Password',
                'from_name' => '{store_name}',
                'html_content' => $this->getPasswordResetTemplate(),
                'plain_text_content' => $this->getPasswordResetPlainText(),
                'variables' => ['customer_name', 'reset_url', 'store_name'],
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'email_verification',
                'name' => 'Email Verification',
                'description' => 'Sent to verify email address',
                'category' => 'account',
                'subject' => 'Verify Your Email Address',
                'from_name' => '{store_name}',
                'html_content' => $this->getEmailVerificationTemplate(),
                'plain_text_content' => $this->getEmailVerificationPlainText(),
                'variables' => ['customer_name', 'verification_url', 'store_name'],
                'is_active' => true,
                'is_system' => true,
            ],

            // Payment Templates
            [
                'code' => 'payment_received',
                'name' => 'Payment Received',
                'description' => 'Sent when payment is successfully received',
                'category' => 'payment',
                'subject' => 'Payment Confirmation - {transaction_id}',
                'from_name' => '{store_name}',
                'html_content' => $this->getPaymentReceivedTemplate(),
                'plain_text_content' => $this->getPaymentReceivedPlainText(),
                'variables' => ['customer_name', 'payment_amount', 'transaction_id', 'payment_date', 'store_name'],
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'payment_failed',
                'name' => 'Payment Failed',
                'description' => 'Sent when payment fails',
                'category' => 'payment',
                'subject' => 'Payment Failed for Order #{order_number}',
                'from_name' => '{store_name}',
                'html_content' => $this->getPaymentFailedTemplate(),
                'plain_text_content' => $this->getPaymentFailedPlainText(),
                'variables' => ['customer_name', 'order_number', 'store_name', 'store_url'],
                'is_active' => true,
                'is_system' => true,
            ],

            // Invoice Templates
            [
                'code' => 'invoice_sent',
                'name' => 'Invoice Email',
                'description' => 'Sent when invoice is sent to customer',
                'category' => 'invoice',
                'subject' => 'Invoice {invoice_number} for Order {order_number}',
                'from_name' => '{store_name}',
                'html_content' => $this->getInvoiceSentTemplate(),
                'plain_text_content' => $this->getInvoiceSentPlainText(),
                'variables' => ['customer_name', 'invoice_number', 'order_number', 'issue_date', 'due_date', 'status', 'total', 'store_name', 'store_url', 'year'],
                'is_active' => true,
                'is_system' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['code' => $template['code'], 'locale' => 'en'],
                $template
            );
        }

        $this->command->info('Email templates seeded successfully.');
    }

    // ========== Order Templates ==========

    private function getOrderPlacedTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4F46E5;">Order Confirmation</h2>
        
        <p>Hi {customer_name},</p>
        
        <p>Thank you for your order! We've received your order and will start processing it right away.</p>
        
        <div style="background-color: #F3F4F6; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Order Number:</strong> #{order_number}</p>
            <p style="margin: 5px 0;"><strong>Order Date:</strong> {order_date}</p>
            <p style="margin: 5px 0;"><strong>Order Total:</strong> {order_total}</p>
        </div>
        
        <p>We'll send you another email when your order ships.</p>
        
        <p>
            <a href="{store_url}/orders/{order_number}" style="display: inline-block; background-color: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Order Details</a>
        </p>
        
        <p>Thank you for shopping with us!</p>
        
        <hr style="border: none; border-top: 1px solid #E5E7EB; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #6B7280;">
            {store_name}<br>
            <a href="{store_url}" style="color: #4F46E5;">{store_url}</a>
        </p>
    </div>
</body>
</html>
HTML;
    }

    private function getOrderPlacedPlainText(): string
    {
        return <<<TEXT
Order Confirmation

Hi {customer_name},

Thank you for your order! We've received your order and will start processing it right away.

Order Details:
- Order Number: #{order_number}
- Order Date: {order_date}
- Order Total: {order_total}

We'll send you another email when your order ships.

View your order: {store_url}/orders/{order_number}

Thank you for shopping with us!

---
{store_name}
{store_url}
TEXT;
    }

    private function getOrderShippedTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4F46E5;">Your Order Has Shipped! 📦</h2>
        
        <p>Hi {customer_name},</p>
        
        <p>Great news! Your order #{order_number} is on its way!</p>
        
        <div style="background-color: #F3F4F6; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Tracking Number:</strong> {tracking_number}</p>
            <p style="margin: 5px 0;"><strong>Shipping Address:</strong><br>{shipping_address}</p>
        </div>
        
        <p>You can track your shipment using the tracking number above.</p>
        
        <p>Thank you for your order!</p>
        
        <hr style="border: none; border-top: 1px solid #E5E7EB; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #6B7280;">{store_name}</p>
    </div>
</body>
</html>
HTML;
    }

    private function getOrderShippedPlainText(): string
    {
        return <<<TEXT
Your Order Has Shipped!

Hi {customer_name},

Great news! Your order #{order_number} is on its way!

Tracking Number: {tracking_number}
Shipping Address: {shipping_address}

You can track your shipment using the tracking number above.

Thank you for your order!

---
{store_name}
TEXT;
    }

    private function getOrderDeliveredTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #10B981;">Order Delivered! ✓</h2>
        
        <p>Hi {customer_name},</p>
        
        <p>Your order #{order_number} has been delivered. We hope you love it!</p>
        
        <p>If you have any questions or concerns, please don't hesitate to contact us.</p>
        
        <p>
            <a href="{store_url}" style="display: inline-block; background-color: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Continue Shopping</a>
        </p>
        
        <p>Thank you for choosing {store_name}!</p>
        
        <hr style="border: none; border-top: 1px solid #E5E7EB; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #6B7280;">{store_name}</p>
    </div>
</body>
</html>
HTML;
    }

    private function getOrderDeliveredPlainText(): string
    {
        return <<<TEXT
Order Delivered!

Hi {customer_name},

Your order #{order_number} has been delivered. We hope you love it!

If you have any questions or concerns, please don't hesitate to contact us.

Continue shopping: {store_url}

Thank you for choosing {store_name}!

---
{store_name}
TEXT;
    }

    private function getOrderCancelledTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #EF4444;">Order Cancelled</h2>
        
        <p>Hi {customer_name},</p>
        
        <p>Your order #{order_number} has been cancelled.</p>
        
        <div style="background-color: #FEF2F2; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Refund Amount:</strong> {order_total}</p>
            <p style="margin: 5px 0;">If applicable, your refund will be processed within 5-7 business days.</p>
        </div>
        
        <p>If you have any questions, please contact our support team.</p>
        
        <hr style="border: none; border-top: 1px solid #E5E7EB; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #6B7280;">{store_name}</p>
    </div>
</body>
</html>
HTML;
    }

    private function getOrderCancelledPlainText(): string
    {
        return <<<TEXT
Order Cancelled

Hi {customer_name},

Your order #{order_number} has been cancelled.

Refund Amount: {order_total}
If applicable, your refund will be processed within 5-7 business days.

If you have any questions, please contact our support team.

---
{store_name}
TEXT;
    }

    // ========== Account Templates ==========

    private function getWelcomeTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4F46E5;">Welcome to {store_name}! 🎉</h2>
        
        <p>Hi {customer_name},</p>
        
        <p>Welcome! We're thrilled to have you as a member of our community.</p>
        
        <p>Your account has been successfully created with the email: <strong>{customer_email}</strong></p>
        
        <p>Here's what you can do now:</p>
        
        <ul>
            <li>Browse our latest products</li>
            <li>Save items to your wishlist</li>
            <li>Track your orders</li>
            <li>Manage your account settings</li>
        </ul>
        
        <p>
            <a href="{store_url}" style="display: inline-block; background-color: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Start Shopping</a>
        </p>
        
        <p>Happy shopping!</p>
        
        <hr style="border: none; border-top: 1px solid #E5E7EB; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #6B7280;">{store_name}</p>
    </div>
</body>
</html>
HTML;
    }

    private function getWelcomePlainText(): string
    {
        return <<<TEXT
Welcome to {store_name}!

Hi {customer_name},

Welcome! We're thrilled to have you as a member of our community.

Your account has been successfully created with the email: {customer_email}

Here's what you can do now:
- Browse our latest products
- Save items to your wishlist
- Track your orders
- Manage your account settings

Start shopping: {store_url}

Happy shopping!

---
{store_name}
TEXT;
    }

    private function getPasswordResetTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4F46E5;">Reset Your Password</h2>
        
        <p>Hi {customer_name},</p>
        
        <p>We received a request to reset your password. Click the button below to create a new password:</p>
        
        <p>
            <a href="{reset_url}" style="display: inline-block; background-color: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a>
        </p>
        
        <p>This link will expire in 60 minutes.</p>
        
        <p>If you didn't request a password reset, please ignore this email or contact support if you have concerns.</p>
        
        <hr style="border: none; border-top: 1px solid #E5E7EB; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #6B7280;">{store_name}</p>
    </div>
</body>
</html>
HTML;
    }

    private function getPasswordResetPlainText(): string
    {
        return <<<TEXT
Reset Your Password

Hi {customer_name},

We received a request to reset your password. Visit the link below to create a new password:

{reset_url}

This link will expire in 60 minutes.

If you didn't request a password reset, please ignore this email or contact support if you have concerns.

---
{store_name}
TEXT;
    }

    private function getEmailVerificationTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4F46E5;">Verify Your Email Address</h2>
        
        <p>Hi {customer_name},</p>
        
        <p>Please verify your email address by clicking the button below:</p>
        
        <p>
            <a href="{verification_url}" style="display: inline-block; background-color: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Verify Email</a>
        </p>
        
        <p>If you didn't create an account with {store_name}, please ignore this email.</p>
        
        <hr style="border: none; border-top: 1px solid #E5E7EB; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #6B7280;">{store_name}</p>
    </div>
</body>
</html>
HTML;
    }

    private function getEmailVerificationPlainText(): string
    {
        return <<<TEXT
Verify Your Email Address

Hi {customer_name},

Please verify your email address by visiting the link below:

{verification_url}

If you didn't create an account with {store_name}, please ignore this email.

---
{store_name}
TEXT;
    }

    // ========== Payment Templates ==========

    private function getPaymentReceivedTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #10B981;">Payment Received ✓</h2>
        
        <p>Hi {customer_name},</p>
        
        <p>We've successfully received your payment.</p>
        
        <div style="background-color: #F0FDF4; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Amount:</strong> {payment_amount}</p>
            <p style="margin: 5px 0;"><strong>Transaction ID:</strong> {transaction_id}</p>
            <p style="margin: 5px 0;"><strong>Date:</strong> {payment_date}</p>
        </div>
        
        <p>Thank you for your payment!</p>
        
        <hr style="border: none; border-top: 1px solid #E5E7EB; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #6B7280;">{store_name}</p>
    </div>
</body>
</html>
HTML;
    }

    private function getPaymentReceivedPlainText(): string
    {
        return <<<TEXT
Payment Received

Hi {customer_name},

We've successfully received your payment.

Payment Details:
- Amount: {payment_amount}
- Transaction ID: {transaction_id}
- Date: {payment_date}

Thank you for your payment!

---
{store_name}
TEXT;
    }

    private function getPaymentFailedTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #EF4444;">Payment Failed</h2>
        
        <p>Hi {customer_name},</p>
        
        <p>Unfortunately, we couldn't process your payment for order #{order_number}.</p>
        
        <p>Please try again or use a different payment method.</p>
        
        <p>
            <a href="{store_url}/orders/{order_number}/payment" style="display: inline-block; background-color: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Retry Payment</a>
        </p>
        
        <p>If you continue to experience issues, please contact our support team.</p>
        
        <hr style="border: none; border-top: 1px solid #E5E7EB; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #6B7280;">{store_name}</p>
    </div>
</body>
</html>
HTML;
    }

    private function getPaymentFailedPlainText(): string
    {
        return <<<TEXT
Payment Failed

Hi {customer_name},

Unfortunately, we couldn't process your payment for order #{order_number}.

Please try again or use a different payment method.

Retry payment: {store_url}/orders/{order_number}/payment

If you continue to experience issues, please contact our support team.

---
{store_name}
TEXT;
    }

    // ========== Invoice Templates ==========

    private function getInvoiceSentTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #3b82f6;
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .invoice-info {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .invoice-info h2 {
            margin-top: 0;
            color: #1e40af;
            font-size: 18px;
        }
        .info-row {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-label {
            font-weight: bold;
            color: #64748b;
            display: inline-block;
            width: 120px;
        }
        .info-value {
            color: #1e293b;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            text-align: center;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice {invoice_number}</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">Thank you for your business!</p>
    </div>

    <div class="content">
        <p>Dear {customer_name},</p>
        
        <p>Please find attached your invoice <strong>{invoice_number}</strong> for order <strong>{order_number}</strong>.</p>

        <div class="invoice-info">
            <h2>Invoice Details</h2>
            
            <div class="info-row">
                <span class="info-label">Invoice Number:</span>
                <span class="info-value">{invoice_number}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Order Number:</span>
                <span class="info-value">{order_number}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Issue Date:</span>
                <span class="info-value">{issue_date}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Due Date:</span>
                <span class="info-value">{due_date}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-badge">{status}</span>
                </span>
            </div>
        </div>

        <div class="total-amount">
            Total Amount: {total}
        </div>

        <p>The detailed invoice is attached as a PDF file. If you have any questions about this invoice, please don't hesitate to contact us.</p>

        <div style="text-align: center;">
            <a href="{store_url}" class="button">Visit Our Store</a>
        </div>
    </div>

    <div class="footer">
        <p><strong>{store_name}</strong></p>
        <p>This is an automated email. Please do not reply directly to this message.</p>
        <p>&copy; {year} {store_name}. All rights reserved.</p>
    </div>
</body>
</html>
HTML;
    }

    private function getInvoiceSentPlainText(): string
    {
        return <<<TEXT
Invoice {invoice_number}

Dear {customer_name},

Please find attached your invoice {invoice_number} for order {order_number}.

Invoice Details:
- Invoice Number: {invoice_number}
- Order Number: {order_number}
- Issue Date: {issue_date}
- Due Date: {due_date}
- Status: {status}

Total Amount: {total}

The detailed invoice is attached as a PDF file. If you have any questions about this invoice, please contact us.

Visit Our Store: {store_url}

{store_name}
This is an automated email. Please do not reply directly to this message.
© {year} {store_name}. All rights reserved.
TEXT;
    }
}
