<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayPal API Credentials
    |--------------------------------------------------------------------------
    |
    | Your PayPal REST API credentials. You can get these from:
    | https://developer.paypal.com/dashboard/applications/live
    |
    */

    'client_id' => env('PAYPAL_CLIENT_ID', ''),
    'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | PayPal Mode
    |--------------------------------------------------------------------------
    |
    | Set to 'sandbox' for testing or 'live' for production.
    |
    */

    'mode' => env('PAYPAL_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Optional webhook ID for signature verification.
    | Get this from: https://developer.paypal.com/dashboard/webhooks
    |
    */

    'webhook_id' => env('PAYPAL_WEBHOOK_ID', ''),
];
