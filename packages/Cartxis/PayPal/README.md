# PayPal Payment Gateway Extension

Accept payments worldwide using PayPal. This extension integrates PayPal Checkout (REST API v2) with your Vortex e-commerce store.

## Features

- ✅ **PayPal Checkout Integration** - Secure redirect-based payment flow
- ✅ **Multiple Payment Methods** - Credit cards, debit cards, PayPal balance
- ✅ **Global Support** - Accept payments from customers worldwide
- ✅ **Sandbox Testing** - Test mode for development
- ✅ **Automatic Order Updates** - Orders updated after successful payment
- ✅ **Refund Support** - Process refunds directly from admin panel
- ✅ **Transaction Logging** - All transactions logged automatically
- ✅ **Webhook Support** - Real-time payment notifications

## Requirements

- PHP ^8.2
- Laravel 11
- Vortex Core ^1.0
- GuzzleHttp/Guzzle ^7.0
- PayPal Business Account

## Installation

### 1. Install Dependencies

This extension uses Guzzle HTTP client to communicate directly with PayPal REST API v2. Dependencies will be installed automatically when you run:

```bash
composer install
```

### 2. Get PayPal API Credentials

1. Go to [PayPal Developer Dashboard](https://developer.paypal.com/dashboard/)
2. Create a new app or select an existing one
3. Copy your **Client ID** and **Client Secret**
4. For testing, use Sandbox credentials
5. For production, use Live credentials

### 3. Configure in Admin Panel

1. Log in to your admin panel
2. Navigate to **Settings** → **Payment Methods**
3. Find **PayPal** in the list
4. Click **Configure**
5. Enter your credentials:
   - **Client ID**: Your PayPal REST API Client ID
   - **Client Secret**: Your PayPal REST API Client Secret
   - **Mode**: Select `Sandbox` for testing or `Live` for production
   - **Webhook ID**: (Optional) For webhook signature verification
6. Click **Save**
7. Toggle the switch to **Enable** the payment method

### 4. Environment Variables (Optional)

You can also configure PayPal using environment variables in your `.env` file:

```env
PAYPAL_CLIENT_ID=your_client_id_here
PAYPAL_CLIENT_SECRET=your_client_secret_here
PAYPAL_MODE=sandbox
PAYPAL_WEBHOOK_ID=your_webhook_id_here
```

### 5. Webhook Configuration (Optional but Recommended)

To receive real-time payment notifications:

1. Go to [PayPal Developer Dashboard → Webhooks](https://developer.paypal.com/dashboard/webhooks)
2. Create a new webhook
3. Set the webhook URL to: `https://yourdomain.com/paypal/webhook`
4. Select these events:
   - `PAYMENT.CAPTURE.COMPLETED`
   - `PAYMENT.CAPTURE.DENIED`
   - `PAYMENT.CAPTURE.REFUNDED`
5. Copy the **Webhook ID** and add it to your configuration

## Usage

### Customer Checkout Flow

1. Customer adds products to cart
2. Proceeds to checkout
3. Selects **PayPal** as payment method
4. Clicks "Place Order"
5. Redirected to PayPal for payment
6. Completes payment on PayPal
7. Redirected back to order success page
8. Order confirmation email sent automatically

### Admin Management

- View order payment status in **Orders** section
- Process refunds if needed (click "Refund" on order details)
- Monitor payment gateway logs in Laravel logs
- View transaction details in **Sales** → **Transactions**

## Payment Methods Supported by PayPal

- **Credit Cards**: Visa, Mastercard, American Express, Discover
- **Debit Cards**: All major debit cards
- **PayPal Balance**: Customer's PayPal wallet
- **Local Payment Methods**: Country-specific methods (varies by region)

## Testing

### Sandbox Mode

1. Set mode to `sandbox` in configuration
2. Use PayPal Sandbox credentials
3. Test with [PayPal Sandbox test accounts](https://developer.paypal.com/dashboard/accounts)
4. Test cards:
   - **Credit Card**: Use any valid test card from PayPal sandbox
   - **PayPal Account**: Create test buyer/seller accounts

### Test Credentials

Create test accounts at: https://developer.paypal.com/dashboard/accounts

## API Implementation

This extension uses direct API calls to PayPal REST API v2 endpoints:

- **Authentication**: OAuth 2.0 (`/v1/oauth2/token`)
- **Create Order**: `POST /v2/checkout/orders`
- **Capture Payment**: `POST /v2/checkout/orders/{id}/capture`
- **Get Order**: `GET /v2/checkout/orders/{id}`
- **Refund**: `POST /v2/payments/captures/{id}/refund`

All requests are authenticated using Bearer token obtained via OAuth 2.0 client credentials flow.

## API Reference

### PayPalGateway Methods

```php
// Process payment
$gateway->processPayment($order, $data);

// Handle callback
$gateway->handleCallback($callbackData);

// Verify payment
$gateway->verifyPayment($order);

// Refund payment
$gateway->refund($order, $amount, $reason);
```

## Troubleshooting

### Payment Redirect Fails

- Check if Client ID and Client Secret are correct
- Ensure mode is set correctly (sandbox vs live)
- Verify callback URL is accessible
- Check Laravel logs for detailed error messages

### Webhook Not Working

- Verify webhook URL is publicly accessible (not localhost)
- Check webhook ID is correctly configured
- Ensure webhook events are properly configured
- Check application logs for webhook processing errors

### Orders Not Updating

- Check callback route is accessible
- Verify order ID is passed correctly
- Check application logs for callback processing errors
- Ensure database transactions are working

### Currency Issues

- PayPal supports 25+ currencies
- Ensure your store currency is supported by PayPal
- Check currency code in order matches PayPal requirements

## Security

- All API credentials are stored encrypted in database
- Callback URLs are validated before processing
- Webhook signatures can be verified (optional)
- Sensitive data is never logged
- All communication with PayPal uses HTTPS

## Support

For issues related to:

- **Extension**: Check Laravel logs and contact support
- **PayPal API**: Visit [PayPal Developer Support](https://developer.paypal.com/support/)
- **Account Issues**: Contact [PayPal Merchant Support](https://www.paypal.com/merchantsupport)

## Resources

- [PayPal REST API Documentation](https://developer.paypal.com/api/rest/)
- [PayPal Checkout Integration](https://developer.paypal.com/docs/checkout/)
- [PayPal Sandbox Testing](https://developer.paypal.com/tools/sandbox/)
- [PayPal Webhook Guide](https://developer.paypal.com/api/rest/webhooks/)

## License

This extension is part of the Vortex e-commerce platform.

## Changelog

### Version 1.0.0 (December 2025)
- Initial release
- PayPal Checkout integration
- Refund support
- Webhook support
- Sandbox testing mode
