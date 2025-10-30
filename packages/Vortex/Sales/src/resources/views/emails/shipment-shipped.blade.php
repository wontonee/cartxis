<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Order Has Been Shipped</title>
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
        .header p {
            margin: 10px 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .shipment-info {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .shipment-info h2 {
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
        .tracking-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .tracking-box h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .tracking-number {
            font-size: 24px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            margin: 10px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0 0;
        }
        .button:hover {
            background-color: #f1f5f9;
        }
        .items-table {
            width: 100%;
            background-color: white;
            border-radius: 5px;
            overflow: hidden;
            margin: 20px 0;
        }
        .items-table th {
            background-color: #f1f5f9;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 14px;
        }
        .icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 15px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="icon">📦</div>
        <h1>Your Order Has Been Shipped!</h1>
        <p>Shipment {{ $shipment->shipment_number }}</p>
    </div>

    <div class="content">
        <p>Hello {{ $order->user?->name ?? 'Customer' }},</p>
        
        <p>Great news! Your order <strong>{{ $order->order_number }}</strong> has been shipped and is on its way to you.</p>

        @if($shipment->tracking_number)
        <div class="tracking-box">
            <h3>Tracking Information</h3>
            <div class="tracking-number">{{ $shipment->tracking_number }}</div>
            <p style="margin: 5px 0;">Carrier: {{ $shipment->carrier ?? 'N/A' }}</p>
            @if($shipment->tracking_url)
            <a href="{{ $shipment->tracking_url }}" class="button">Track Your Package</a>
            @endif
        </div>
        @endif

        <div class="shipment-info">
            <h2>Shipment Details</h2>
            <div class="info-row">
                <span class="info-label">Shipment Number:</span>
                <span class="info-value">{{ $shipment->shipment_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Order Number:</span>
                <span class="info-value">{{ $order->order_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Shipped Date:</span>
                <span class="info-value">{{ $shipment->shipped_at?->format('F j, Y, g:i a') ?? now()->format('F j, Y, g:i a') }}</span>
            </div>
            @if($shipment->carrier)
            <div class="info-row">
                <span class="info-label">Carrier:</span>
                <span class="info-value">{{ $shipment->carrier }}</span>
            </div>
            @endif
        </div>

        <h3 style="margin-top: 30px;">Shipped Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th style="text-align: right;">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shipment->shipmentItems as $item)
                <tr>
                    <td>{{ $item->orderItem->product->name ?? 'Product' }}</td>
                    <td>{{ $item->orderItem->product->sku ?? 'N/A' }}</td>
                    <td style="text-align: right;">{{ $item->quantity }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top: 30px;">
            Your package should arrive within the estimated delivery time provided by the carrier. 
            You can use the tracking number above to monitor your shipment's progress.
        </p>

        <p>If you have any questions about your order or shipment, please don't hesitate to contact our customer support team.</p>

        <p style="margin-top: 30px;">
            Thank you for your order!<br>
            <strong>The Vortex Team</strong>
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} Vortex. All rights reserved.</p>
        <p>This is an automated email. Please do not reply to this message.</p>
    </div>
</body>
</html>
