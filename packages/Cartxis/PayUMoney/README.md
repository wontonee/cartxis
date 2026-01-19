# PayUMoney Payment Gateway Extension

Accept payments in India using PayUMoney (PayU India). This extension integrates PayUMoney with your Vortex e-commerce store, supporting all major Indian payment methods.

## Features

- ✅ **PayUMoney Integration** - Secure payment gateway for Indian merchants
- ✅ **Multiple Payment Methods** - Cards, Net Banking, UPI, Wallets
- ✅ **Hash-Based Security** - SHA-512 hash verification for secure transactions
- ✅ **Test Mode** - Sandbox environment for development
- ✅ **Automatic Order Updates** - Orders updated after successful payment
- ✅ **Transaction Logging** - All transactions logged automatically
- ✅ **Mobile Optimized** - Responsive payment pages
- ✅ **Instant Callbacks** - Real-time payment status updates

## Requirements

- PHP ^8.2
- Laravel 11
- Vortex Core ^1.0
- PayUMoney Merchant Account (Indian business only)

## Installation

### 1. Install Dependencies

The required dependencies will be installed automatically when you run:

```bash
composer install
```

### 2. Get PayUMoney Merchant Credentials

1. Sign up for a PayUMoney merchant account at [PayUMoney](https://www.payumoney.com/)
2. Complete KYC verification (required for live transactions)
3. Log in to [PayUMoney Merchant Dashboard](https://www.payumoney.com/merchant-dashboard/)
4. Go to **Settings** → **API Keys**
5. Copy your **Merchant Key** and **Merchant Salt**

### 3. Configure in Admin Panel

1. Log in to your admin panel
2. Navigate to **Settings** → **Payment Methods**
3. Find **PayUMoney** in the list
4. Click **Configure**
5. Enter your credentials:
   - **Merchant Key**: Your PayUMoney Merchant Key
   - **Merchant Salt**: Your PayUMoney Merchant Salt (keep this secret!)
   - **Mode**: Select `Test` for testing or `Production` for live transactions
   - **Authorization Header**: (Optional) Additional auth header if required
6. Click **Save**
7. Toggle the switch to **Enable** the payment method

### 4. Environment Variables (Optional)

You can also configure PayUMoney using environment variables in your `.env` file:

```env
PAYUMONEY_MERCHANT_KEY=your_merchant_key_here
PAYUMONEY_MERCHANT_SALT=your_merchant_salt_here
PAYUMONEY_MODE=test
PAYUMONEY_AUTH_HEADER=
```

### 5. Test Mode Configuration

For testing:

1. Use test merchant credentials provided by PayUMoney
2. Set mode to `test`
3. Use test cards provided by PayUMoney
4. Test payment flow end-to-end

**Important**: Test mode uses different payment URL (`https://test.payu.in/_payment`)

## Usage

### Customer Checkout Flow

1. Customer adds products to cart
2. Proceeds to checkout
3. Selects **PayUMoney** as payment method
4. Clicks "Place Order"
5. Redirected to PayUMoney payment page
6. Selects payment method (card, UPI, wallet, etc.)
7. Completes payment
8. Redirected back to order success page
9. Order confirmation email sent automatically

### Admin Management

- View order payment status in **Orders** section
- Monitor payment gateway logs in Laravel logs
- View transaction details in **Sales** → **Transactions**
- Refunds must be processed through PayUMoney dashboard

## Payment Methods Supported

PayUMoney supports all major Indian payment methods:

- **Credit Cards**: Visa, Mastercard, American Express, Diners Club, RuPay
- **Debit Cards**: All major banks (Visa, Mastercard, Maestro, RuPay)
- **Net Banking**: 50+ banks including ICICI, HDFC, SBI, Axis, Kotak
- **UPI**: All UPI apps (Google Pay, PhonePe, Paytm, BHIM)
- **Wallets**: Paytm, Mobikwik, Freecharge, Ola Money, JioMoney
- **EMI**: Easy monthly installments (select cards)
- **PayUMoney Wallet**: Customer's PayUMoney balance

## Testing

### Test Mode

1. Set mode to `test` in configuration
2. Use PayUMoney test credentials
3. Access test payment page at `https://test.payu.in/_payment`

### Test Cards

Use these test cards in test mode:

**Success Transaction:**
- Card Number: `5123456789012346`
- CVV: `123`
- Expiry: Any future date
- Name: Any name

**Failed Transaction:**
- Card Number: `4111111111111111`
- CVV: `123`
- Expiry: Any future date
- Name: Any name

For more test cards, visit: [PayU Test Cards](https://devguide.payu.in/test-cards/)

## Hash Generation

PayUMoney uses SHA-512 hash for security. The extension automatically:

1. **Request Hash**: Generated before payment with merchant salt
2. **Response Hash**: Verified on callback for authenticity
3. **Hash Sequence**: `key|txnid|amount|productinfo|firstname|email|udf1|...|salt`

**Important**: Never share your merchant salt publicly!

## API Reference

### PayUMoneyGateway Methods

```php
// Process payment
$gateway->processPayment($order, $data);

// Handle callback (success/failure)
$gateway->handleCallback($callbackData);

// Verify payment
$gateway->verifyPayment($order);

// Refund (manual process)
$gateway->refund($order, $amount, $reason);
```

## Troubleshooting

### Payment Redirect Fails

- Check if Merchant Key and Salt are correct
- Ensure mode is set correctly (test vs production)
- Verify callback URL is accessible
- Check Laravel logs for detailed error messages

### Hash Verification Fails

- Ensure Merchant Salt is correct (case-sensitive)
- Check if all parameters are passed correctly
- Verify hash calculation sequence
- Check application logs for hash comparison details

### Orders Not Updating

- Verify callback route is accessible
- Check order ID (udf1) is passed correctly
- Ensure hash verification is passing
- Check application logs for callback processing errors

### Payment Status Shows Pending

- Check if callback was received and processed
- Verify hash verification passed
- Check transaction logs in admin panel
- Contact PayUMoney support if issue persists

## Refunds

PayUMoney refunds must be processed manually:

1. Log in to [PayUMoney Merchant Dashboard](https://www.payumoney.com/merchant-dashboard/)
2. Go to **Transactions** → **All Transactions**
3. Find the transaction to refund
4. Click **Refund** and follow the process
5. Refund will be processed within 5-7 business days

**Note**: The extension's refund method is a placeholder. Actual refunds must be initiated from PayUMoney dashboard.

## Security

- Merchant Salt is stored encrypted in database
- All payment requests use SHA-512 hash
- Response hash is verified before order update
- Callback data is validated before processing
- Sensitive data is never logged
- All communication uses HTTPS

## Important Notes

1. **Indian Business Only**: PayUMoney is only for Indian businesses with valid GSTIN
2. **KYC Required**: Complete KYC verification before going live
3. **Currency**: Only INR (Indian Rupees) supported
4. **Settlement**: Payments settled to bank account (T+2 or T+3 days)
5. **Transaction Charges**: Check PayUMoney pricing for current rates

## Support

For issues related to:

- **Extension**: Check Laravel logs and contact support
- **PayUMoney API**: Email support@payumoney.com
- **Account Issues**: Contact PayUMoney merchant support at 0120-3073073

## Resources

- [PayUMoney Developer Guide](https://devguide.payu.in/)
- [PayUMoney Merchant Dashboard](https://www.payumoney.com/merchant-dashboard/)
- [PayUMoney Test Environment](https://test.payu.in/)
- [PayU Integration Guide](https://devguide.payu.in/web-integration/)

## License

This extension is part of the Vortex e-commerce platform.

## Changelog

### Version 1.0.0 (December 2025)
- Initial release
- PayUMoney integration with hash verification
- Support for all Indian payment methods
- Test mode support
- Transaction logging
- Automatic order updates
