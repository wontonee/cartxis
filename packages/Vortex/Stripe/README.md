# Stripe Payment Gateway Extension

A complete Stripe payment gateway integration for Vortex Commerce Platform.

## Features

- ✅ **Secure Payment Processing** - PCI-compliant payment handling via Stripe
- ✅ **Multiple Payment Methods** - Cards, Digital Wallets (Apple Pay, Google Pay), and Regional Methods
- ✅ **3D Secure Support** - Enhanced security for card transactions
- ✅ **Payment Method Storage** - Allow customers to save payment methods for future purchases
- ✅ **Webhook Handling** - Real-time payment status updates
- ✅ **Refund Management** - Process refunds directly from admin panel
- ✅ **Multi-currency** - Support for any currency accepted by Stripe
- ✅ **Developer Friendly** - Hooks and events for custom integrations

## Installation

### 1. Install via Composer

```bash
cd /path/to/vortex
composer require vortex/stripe
```

### 2. Environment Configuration

Add the following to your `.env` file:

```env
STRIPE_PUBLIC_KEY=pk_live_your_public_key
STRIPE_SECRET_KEY=sk_live_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
STRIPE_CURRENCY=usd
STRIPE_ENABLE_3D_SECURE=true
```

### 3. Get Your Keys

1. Go to [Stripe Dashboard](https://dashboard.stripe.com)
2. Navigate to **Settings** → **API Keys**
3. Copy your **Publishable Key** and **Secret Key**
4. For webhooks, create an endpoint at `https://yoursite.com/webhooks/stripe`

### 4. Activate the Extension

```bash
# Run migrations if any
php artisan migrate

# Clear cache
php artisan optimize:clear
```

Then activate "Stripe Payment Gateway" from the admin panel under Settings → Payment Methods.

## Configuration

### Admin Panel

1. Go to **Settings** → **Payment Methods**
2. Click **Configure** on the Stripe card
3. Add your Stripe Publishable Key
4. Enable/disable features:
   - **3D Secure** - For enhanced security
   - **Save Payment Methods** - For recurring/saved payments
5. Select which payment methods to enable (Cards, Apple Pay, Google Pay, iDEAL, etc.)
6. Click **Save Configuration**

### Webhook Configuration

To receive real-time payment updates:

1. Go to Stripe Dashboard → **Developers** → **Webhooks**
2. Click **Add an endpoint**
3. Endpoint URL: `https://yoursite.com/webhooks/stripe`
4. Select events:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
5. Copy the **Webhook Secret** and add to `.env` as `STRIPE_WEBHOOK_SECRET`

## Usage

### In Your Code

#### Listen to Payment Events

```php
use Vortex\Core\Facades\Hook;

// Payment succeeded
Hook::register('stripe.payment_succeeded', function ($paymentIntent) {
    $order = Order::find($paymentIntent->metadata->order_id);
    $order->markAsPaid();
});

// Payment failed
Hook::register('stripe.payment_failed', function ($paymentIntent) {
    $order = Order::find($paymentIntent->metadata->order_id);
    $order->markAsFailed();
});

// Charge refunded
Hook::register('stripe.charge_refunded', function ($charge) {
    $order = Order::find($charge->metadata->order_id);
    $order->markAsRefunded();
});
```

#### Create Payment Intent (Frontend)

```javascript
// Create payment intent
const response = await fetch('/admin/payment-methods/stripe/create-payment-intent', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        amount: 99.99,
        currency: 'usd',
        order_id: 'order_123',
        customer_email: 'customer@example.com',
        description: 'Order #123 - Website Purchase',
    }),
});

const { client_secret, payment_intent_id } = await response.json();

// Use with Stripe.js to confirm payment
const { error, paymentIntent } = await stripe.confirmCardPayment(client_secret);
```

## Supported Payment Methods

### Cards
- Visa
- Mastercard
- American Express
- Discover
- Diners Club
- JCB

### Digital Wallets
- Apple Pay
- Google Pay

### Regional Methods
- **iDEAL** - Netherlands
- **Bancontact** - Belgium
- **EPS** - Austria
- **Giropay** - Germany
- **Przelewy24** - Poland
- **Klarna** - Buy Now Pay Later
- **Alipay** - China

## Security Features

### PCI Compliance
- No card data is stored on your server
- All card processing happens through Stripe's secure infrastructure

### 3D Secure (3DS)
- Optional but recommended for enhanced security
- Adds an extra layer of authentication
- Reduces fraud and chargebacks

### Webhook Verification
- All webhook events are cryptographically verified
- Prevents unauthorized requests

## Configuration Options

### config/stripe.php

```php
return [
    'stripe' => [
        'public_key' => env('STRIPE_PUBLIC_KEY'),
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'currency' => env('STRIPE_CURRENCY', 'usd'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'api_version' => env('STRIPE_API_VERSION', '2023-10-16'),
        'enable_3d_secure' => env('STRIPE_ENABLE_3D_SECURE', true),
        'save_payment_method' => env('STRIPE_SAVE_PAYMENT_METHOD', true),
    ],
    // ... more options
];
```

## Troubleshooting

### "Invalid API Key" Error

- Check that `STRIPE_SECRET_KEY` is correctly set in `.env`
- Ensure you're using your **Secret Key**, not your Publishable Key
- Verify the key is for the correct environment (test vs. live)

### Webhooks Not Working

- Check that `STRIPE_WEBHOOK_SECRET` is set in `.env`
- Verify your endpoint URL is accessible from the internet
- Check webhook logs in Stripe Dashboard

### Payment Intent Creation Fails

- Ensure `STRIPE_PUBLIC_KEY` is set
- Verify amount is in correct format (cents for USD)
- Check API rate limits

## Support

- **Documentation**: https://docs.vortexcommerce.com/stripe
- **Stripe Docs**: https://stripe.com/docs
- **Issues**: https://github.com/vortex/stripe/issues

## License

MIT License - see LICENSE file for details
