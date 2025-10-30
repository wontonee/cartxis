<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-label {
            font-weight: bold;
            color: #64748b;
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
        .button:hover {
            background-color: #2563eb;
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
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-sent {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice {{ $invoice->invoice_number }}</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">Thank you for your business!</p>
    </div>

    <div class="content">
        <p>Dear {{ $order->customer_name ?? 'Valued Customer' }},</p>
        
        <p>Please find attached your invoice <strong>{{ $invoice->invoice_number }}</strong> for order <strong>{{ $order->order_number }}</strong>.</p>

        <div class="invoice-info">
            <h2>Invoice Details</h2>
            
            <div class="info-row">
                <span class="info-label">Invoice Number:</span>
                <span class="info-value">{{ $invoice->invoice_number }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Order Number:</span>
                <span class="info-value">{{ $order->order_number }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Issue Date:</span>
                <span class="info-value">{{ $invoice->issue_date->format('F d, Y') }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Due Date:</span>
                <span class="info-value">{{ $invoice->due_date->format('F d, Y') }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ strtoupper($invoice->status) }}
                    </span>
                </span>
            </div>
        </div>

        <div class="total-amount">
            Total Amount: ₹{{ number_format($invoice->total, 2) }}
        </div>

        @if($invoice->notes)
        <div class="invoice-info">
            <h2>Notes</h2>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif

        <p>The detailed invoice is attached as a PDF file. If you have any questions about this invoice, please don't hesitate to contact us.</p>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}" class="button">Visit Our Store</a>
        </div>
    </div>

    <div class="footer">
        <p><strong>Vortex</strong></p>
        <p>This is an automated email. Please do not reply directly to this message.</p>
        <p>&copy; {{ date('Y') }} Vortex. All rights reserved.</p>
    </div>
</body>
</html>
