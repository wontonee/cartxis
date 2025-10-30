<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Order Has Been Delivered</title>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        .delivery-info {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #10b981;
        }
        .delivery-info h2 {
            margin-top: 0;
            color: #047857;
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
        .success-box {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 30px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .success-box h3 {
            margin: 0 0 10px 0;
            font-size: 20px;
        }
        .success-box p {
            margin: 5px 0;
            font-size: 16px;
            opacity: 0.95;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: white;
            color: #10b981;
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
        .feedback-box {
            background-color: #fef3c7;
            border: 2px solid #fbbf24;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .feedback-box h3 {
            margin: 0 0 10px 0;
            color: #92400e;
        }
        .feedback-box p {
            margin: 10px 0;
            color: #78350f;
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
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="icon">✅</div>
        <h1>Your Order Has Been Delivered!</h1>
        <p>Shipment {{ $shipment->shipment_number }}</p>
    </div>

    <div class="content">
        <p>Hello {{ $order->user?->name ?? 'Customer' }},</p>
        
        <div class="success-box">
            <h3>🎉 Delivery Confirmed</h3>
            <p>Your order <strong>{{ $order->order_number }}</strong> has been successfully delivered.</p>
            <p><strong>Delivered on:</strong> {{ $shipment->delivered_at?->format('F j, Y, g:i a') ?? now()->format('F j, Y, g:i a') }}</p>
        </div>

        <p>We hope you're excited about your purchase! Your package should now be at the delivery address.</p>

        <div class="delivery-info">
            <h2>Delivery Details</h2>
            <div class="info-row">
                <span class="info-label">Shipment Number:</span>
                <span class="info-value">{{ $shipment->shipment_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Order Number:</span>
                <span class="info-value">{{ $order->order_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Delivered Date:</span>
                <span class="info-value">{{ $shipment->delivered_at?->format('F j, Y, g:i a') ?? now()->format('F j, Y, g:i a') }}</span>
            </div>
            @if($shipment->carrier)
            <div class="info-row">
                <span class="info-label">Carrier:</span>
                <span class="info-value">{{ $shipment->carrier }}</span>
            </div>
            @endif
            @if($shipment->tracking_number)
            <div class="info-row">
                <span class="info-label">Tracking Number:</span>
                <span class="info-value">{{ $shipment->tracking_number }}</span>
            </div>
            @endif
        </div>

        <h3 style="margin-top: 30px;">Delivered Items</h3>
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

        <div class="feedback-box">
            <h3>⭐ We'd Love Your Feedback!</h3>
            <p>How was your experience? Your feedback helps us improve our service.</p>
            <a href="#" class="button" style="background-color: #fbbf24; color: #78350f;">Leave a Review</a>
        </div>

        <p style="margin-top: 30px;">
            If you have any issues with your delivery or the items you received, please contact our customer support team immediately. 
            We're here to help!
        </p>

        <p>Thank you for choosing Vortex. We look forward to serving you again!</p>

        <p style="margin-top: 30px;">
            Best regards,<br>
            <strong>The Vortex Team</strong>
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} Vortex. All rights reserved.</p>
        <p>This is an automated email. Please do not reply to this message.</p>
    </div>
</body>
</html>
