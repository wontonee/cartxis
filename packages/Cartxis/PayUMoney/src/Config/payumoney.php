<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayUMoney Merchant Credentials
    |--------------------------------------------------------------------------
    |
    | Your PayUMoney merchant credentials. You can get these from:
    | https://www.payumoney.com/merchant-dashboard/
    |
    */

    'merchant_key' => env('PAYUMONEY_MERCHANT_KEY', ''),
    'merchant_salt' => env('PAYUMONEY_MERCHANT_SALT', ''),

    /*
    |--------------------------------------------------------------------------
    | PayUMoney Mode
    |--------------------------------------------------------------------------
    |
    | Set to 'test' for testing or 'production' for live transactions.
    |
    */

    'mode' => env('PAYUMONEY_MODE', 'test'),

    /*
    |--------------------------------------------------------------------------
    | Authorization Header
    |--------------------------------------------------------------------------
    |
    | Optional authorization header for API requests.
    |
    */

    'auth_header' => env('PAYUMONEY_AUTH_HEADER', ''),

    /*
    |--------------------------------------------------------------------------
    | Payment URLs
    |--------------------------------------------------------------------------
    |
    | PayUMoney payment gateway URLs.
    |
    */

    'test_url' => 'https://test.payu.in/_payment',
    'live_url' => 'https://secure.payu.in/_payment',
];
