# Razorpay Payment Gateway

Razorpay payment gateway integration for Vortex e-commerce platform.

## Features

- 💳 Accept payments via Razorpay
- 🔐 Secure payment processing with signature verification
- 🔔 Webhook support for automated order updates
- 🌐 Support for multiple payment methods (Cards, UPI, Netbanking, Wallets)
- 💰 Automatic payment capture
- 🔄 Refund support
- 📧 Automatic order confirmation emails

## Installation

1. The package is already installed via Composer:
   ```bash
   composer require razorpay/razorpay
   ```

2. Register the service provider in `bootstrap/providers.php`:
   ```php
   return [
       // ... other providers
       Vortex\Razorpay\Providers\RazorpayServiceProvider::class,
   ];
   ```

3. Clear application cache:
   ```bash
   php artisan cache:clear
   ```

## Configuration

### 1. Get Razorpay API Keys

1. Sign up at [Razorpay Dashboard](https://dashboard.razorpay.com/)
2. Navigate to **Settings > API Keys**
3. Generate or copy your **Key ID** and **Key Secret**

### 2. Configure in Admin Panel

1. Log in to your admin panel
2. Navigate to **Settings > Payment Methods**
3. Find **Razorpay** in the list
4. Click **Configure**
5. Enter your credentials:
   - **Key ID**: Your Razorpay Key ID
   - **Key Secret**: Your Razorpay Key Secret
   - **Currency**: INR (Indian Rupee - default)
6. Enable the payment method
7. Save configuration

### 3. Environment Variables (Optional)

You can also configure via `.env` file:

```env
RAZORPAY_KEY_ID=your_key_id_here
RAZORPAY_KEY_SECRET=your_key_secret_here
RAZORPAY_CURRENCY=INR
RAZORPAY_AUTO_CAPTURE=true
```

### 4. Webhook Configuration

To receive automatic payment updates:

1. Go to [Razorpay Dashboard > Settings > Webhooks](https://dashboard.razorpay.com/app/webhooks)
2. Click **Create New Webhook**
3. Enter webhook URL: `https://yourdomain.com/webhooks/razorpay`
4. Select events to listen:
   - `payment.captured`
   - `payment.failed`
   - `refund.created`
5. Copy the **Webhook Secret**
6. Add to your configuration:
   ```env
   RAZORPAY_WEBHOOK_SECRET=your_webhook_secret_here
   ```

## Usage

### Customer Checkout Flow

1. Customer adds products to cart
2. Proceeds to checkout
3. Selects **Razorpay** as payment method
4. Clicks "Place Order"
5. Razorpay checkout modal opens
6. Customer completes payment
7. Redirected to order success page
8. Order confirmation email sent automatically

### Admin Management

- View order payment status in **Orders** section
- Process refunds if needed
- Monitor payment gateway logs

## Payment Methods Supported

- **Cards**: Credit and Debit cards (Visa, Mastercard, Maestro, RuPay)
- **UPI**: All UPI apps (Google Pay, PhonePe, Paytm, etc.)
- **Netbanking**: All major Indian banks
- **Wallets**: Paytm, Mobikwik, Freecharge, etc.
- **EMI**: Easy monthly installments (configurable)

## Configuration Options

| Option | Description | Default |
|--------|-------------|---------|
| `key_id` | Razorpay Key ID | - |
| `key_secret` | Razorpay Key Secret | - |
| `currency` | Payment currency | INR |
| `webhook_secret` | Webhook signature secret | - |
| `auto_capture` | Auto-capture payments | true |

## Testing

### Test Mode

Razorpay provides test credentials for development:

1. Use test API keys from dashboard
2. Use test cards for payment:
   - Card: `4111 1111 1111 1111`
   - CVV: Any 3 digits
   - Expiry: Any future date

### Test Webhooks

Use [Razorpay Webhook Simulator](https://dashboard.razorpay.com/app/webhooks/simulator) to test webhook events.

## API Reference

### RazorpayGateway Methods

```php
// Process payment
$gateway->processPayment($order, $data);

// Handle callback
$gateway->handleCallback($callbackData);

// Handle webhook
$gateway->handleWebhook($payload, $signature);

// Refund payment
$gateway->refund($order, $amount);
```

## Troubleshooting

### Payment verification fails

- Check if Key ID and Key Secret are correct
- Ensure signature verification is not blocked
- Check server logs for detailed error messages

### Webhook not working

- Verify webhook URL is publicly accessible
- Check webhook secret is correctly configured
- Ensure webhook signature verification is enabled

### Orders not updating

- Check webhook events are properly configured
- Verify order ID is passed in payment notes
- Check application logs for webhook processing errors

## Security

- All API credentials are stored encrypted in database
- Webhook signatures are verified before processing
- Payment signatures are validated on callback
- Sensitive data is never logged

## Support

For Razorpay-specific issues:
- [Razorpay Documentation](https://razorpay.com/docs/)
- [Razorpay Support](https://razorpay.com/support/)

For integration issues:
- Check application logs: `storage/logs/laravel.log`
- Contact your development team

## License

This package is part of the Vortex e-commerce platform.
