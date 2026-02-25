# PhonePe Payment Gateway SDK for PHP

The PhonePe Payment Gateway SDK for PHP provides a convenient way to integrate with PhonePe's payment gateway APIs in your PHP applications. This SDK simplifies the process of initiating payments, checking transaction statuses, and handling callbacks.

## Requirements

- PHP 8.2 or later
- [Composer](https://getcomposer.org/) for dependency management

## Installation

### Mandatory Setup

Before proceeding with the installation steps below, you must add the following repository details to your project’s root composer.json file. This step is required to fetch the PhonePe PHP SDK package.

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
    ],

    "require": {
        "phonepe/pg-php-sdk-v2": "^2.0",
        "vlucas/phpdotenv": "^5.6",
        "netresearch/jsonmapper": "^4.4"
    }
}
```

You can add the PhonePe PG SDK for PHP as a dependency to your project using Composer.

```bash
composer install
```

## Initialization

To start using the SDK, you need to initialize the `StandardCheckoutClient`. You'll need your `clientId`, `clientSecret`, and `clientVersion` from PhonePe.

```php
use PhonePe\payments\v2\standardCheckout\StandardCheckoutClient;
use PhonePe\Env;

$clientId = "YOUR_CLIENT_ID";
$clientSecret = "YOUR_CLIENT_SECRET";
$clientVersion = "YOUR_CLIENT_VERSION";
$env = Env::UAT; // Or Env::PRODUCTION

$phonepeClient = StandardCheckoutClient::getInstance($clientId, $clientVersion, $clientSecret, $env);
```

## Standard Checkout

The Standard Checkout flow allows you to redirect your users to the PhonePe payment page to complete their transactions.

### 1. Initiate a Payment

First, create a `StandardCheckoutPayRequest` with a unique order ID, the amount, and a redirect URL.

```php
use PhonePe\payments\v2\models\request\builders\StandardCheckoutPayRequestBuilder;

$merchantOrderId = "ORDER-" . uniqid();
$amount = 1000; // Amount in paise (e.g., 1000 for ₹10.00)
$redirectUrl = "https://your-website.com/payment-redirect";
$message = "Payment for order " . $merchantOrderId;


$payRequest = (new StandardCheckoutPayRequestBuilder())
    ->merchantOrderId($merchantOrderId)
    ->amount($amount)
    ->redirectUrl($redirectUrl)
    ->message($message)
    ->build();
```

### 2. Make the `pay` API Call

Next, call the `pay` method with the request object to get a payment response.

```php
try {
    $payResponse = $phonepeClient->pay($payRequest);
    $redirectUrl = $payResponse->getRedirectUrl();
    // Redirect the user to the $redirectUrl to complete the payment
    header("Location: " . $redirectUrl);
    exit();
} catch (PhonePe\common\exceptions\PhonePeException $e) {
    // Handle exceptions
    echo $e->getMessage();
}
```

The `pay` method will return a `StandardCheckoutPayResponse` containing a `redirectUrl`. You should redirect your user to this URL to complete the payment.

## Check Order Status

You can check the status of a transaction using the `getOrderStatus` method with the merchant's order ID.

```php
try {
    $statusResponse = $phonepeClient->getOrderStatus($merchantOrderId);
    // Process the status response
    echo "Payment state: " . $statusResponse->getState();
} catch (PhonePe\common\exceptions\PhonePeException $e) {
    // Handle exceptions
    echo $e->getMessage();
}
```

## Handle Callbacks

PhonePe will send server-to-server callbacks to your specified callback URL to notify you of the payment status. The SDK provides a method to verify the authenticity of these callbacks.

```php
// Get headers and body from the callback request
$headers = [];
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0) {
        $headerKey = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
        $headers[$headerKey] = $value;
    }
}
$requestBody = file_get_contents('php://input');
$username = "YOUR_CALLBACK_USERNAME";
$password = "YOUR_CALLBACK_PASSWORD";


try {
    $callbackResponse = $phonepeClient->verifyCallbackResponse($headers, $requestBody, $username, $password);
    // Process the callback response
    echo "Callback state: " . $callbackResponse->getState();
} catch (PhonePe\common\exceptions\PhonePeException $e) {
    // Handle exceptions
    echo $e->getMessage();
}
```
**Note:** The `getallheaders()` function is not available in all PHP environments (e.g., Nginx). The example above provides a more reliable way to get the headers. In the `$headers` array, the `Authorization` header key will be available as `Authorization`.

## Refunds

You can initiate a refund for a transaction using the `refund` method.

### 1. Initiate a Refund

```php
use PhonePe\payments\v2\models\request\builders\StandardCheckoutRefundRequestBuilder;

$merchantRefundId = "REFUND-" . uniqid();
$originalMerchantOrderId = "ORDER-12345"; // The original order ID
$amount = 500; // Amount to refund in paise

$refundRequest = (new StandardCheckoutRefundRequestBuilder())
    ->merchantRefundId($merchantRefundId)
    ->originalMerchantOrderId($originalMerchantOrderId)
    ->amount($amount)
    ->build();

try {
    $refundResponse = $phonepeClient->refund($refundRequest);
    // Process the refund response
    echo "Refund state: " . $refundResponse->getState();
} catch (PhonePe\common\exceptions\PhonePeException $e) {
    // Handle exceptions
    echo $e->getMessage();
}
```

### 2. Check Refund Status

You can check the status of a refund using the `getRefundStatus` method.

```php
try {
    $refundStatusResponse = $phonepeClient->getRefundStatus($merchantRefundId);
    // Process the refund status response
    echo "Refund status: " . $refundStatusResponse->getState();
} catch (PhonePe\common\exceptions\PhonePeException $e) {
    // Handle exceptions
    echo $e->getMessage();
}
```

## Documentation

For detailed API documentation, advanced features, and integration options:

- [PHP SDK Documentation](https://developer.phonepe.com/payment-gateway/backend-sdk/php-be-sdk/introduction)
- [PhonePe Developer Portal](https://developer.phonepe.com/)

## Contributing

Contributions to the PhonePe PG SDK for PHP are welcome. Here's how you can contribute:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure your code follows the project's coding standards and includes appropriate tests.

## License

This project is licensed under the Apache License 2.0 - see the [LICENSE](LICENSE) file for details.

```
Copyright 2025 PhonePe Private Limited

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```
