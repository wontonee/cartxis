# PhonePe Payment Gateway for Cartxis

Accept payments via PhonePe - India's leading digital payments platform. This extension supports UPI, credit cards, debit cards, net banking, and wallets.

## Features

- 🔐 **Secure Payments**: End-to-end encrypted transactions
- 📱 **UPI Support**: Accept UPI payments directly
- 💳 **Card Payments**: Credit and debit card support
- 🏦 **Net Banking**: Support for all major Indian banks
- 💰 **Wallets**: PhonePe wallet and other digital wallets
- 🔄 **Refunds**: Full and partial refund support
- 📊 **Order Status**: Real-time payment status verification
- 🔔 **Webhooks**: Automatic payment confirmation via callbacks

## Requirements

- PHP 8.2 or higher
- Cartxis Core 1.0+
- PhonePe Business Account
- Production credentials (Client ID, Client Secret, Client Version)

## Installation

### 1. Add PhonePe SDK Repository

Add the following to your project's root `composer.json`:

```json
{
  "repositories": [
    {
      "type": "package",
      "url": "./vendor/phonepe/pg-sdk-php/",
      "package": {
        "name": "phonepe/pg-php-sdk-v2",
        "version": "2.0.0",
        "dist": {
          "url": "https://phonepe.mycloudrepo.io/public/repositories/phonepe-pg-php-sdk/v2-sdk.zip",
          "type": "zip"
        },
        "autoload": {
          "classmap": ["/"]
        }
      }
    }
  ]
}
```

### 2. Install Dependencies

```bash
composer require phonepe/pg-php-sdk-v2 vlucas/phpdotenv netresearch/jsonmapper
```

### 3. Sync Extensions

```bash
php artisan cartxis:extensions:sync
```

### 4. Configure in Admin Panel

1. Navigate to **Settings > Payment Methods** in the admin panel
2. Find **PhonePe** and click **Configure**
3. Enter your credentials:
   - **Client ID**: Your PhonePe Client ID
   - **Client Secret**: Your PhonePe Client Secret
   - **Client Version**: Your PhonePe Client Version
   - **Callback Username**: Username for webhook authentication
   - **Callback Password**: Password for webhook authentication
4. Enable the payment method

## Environment Variables (Optional)

You can also configure via `.env` file:

```env
PHONEPE_CLIENT_ID=your_client_id
PHONEPE_CLIENT_SECRET=your_client_secret
PHONEPE_CLIENT_VERSION=1
PHONEPE_CALLBACK_USERNAME=your_callback_username
PHONEPE_CALLBACK_PASSWORD=your_callback_password
```

## Webhook Setup

Configure the webhook URL in your PhonePe Business Dashboard:

```
https://yourdomain.com/phonepe/webhook
```

Ensure you set the callback username and password in both the PhonePe dashboard and your Cartxis admin panel.

## Payment Flow

1. Customer selects PhonePe at checkout
2. Customer is redirected to PhonePe payment page
3. Customer completes payment (UPI, card, etc.)
4. Customer is redirected back to your store
5. Webhook confirms payment status
6. Order is marked as paid

## Supported Payment Methods

| Method | Description |
|--------|-------------|
| UPI | Direct UPI payments via PhonePe |
| Credit Card | All major credit cards |
| Debit Card | All major debit cards |
| Net Banking | All major Indian banks |
| Wallet | PhonePe wallet |

## Troubleshooting

### Payment Not Processing

1. Verify your credentials are correct
2. Ensure the extension is active
3. Check Laravel logs for errors

### Webhook Not Receiving

1. Verify webhook URL is accessible
2. Check callback credentials match
3. Ensure SSL certificate is valid

## Support

- **Documentation**: [PhonePe Developer Docs](https://developer.phonepe.com)
- **Issues**: Report issues in the Cartxis GitHub repository
- **Email**: support@cartxis-commerce.com

## License

This extension is part of the Cartxis Commerce platform and is licensed under the MIT license.
