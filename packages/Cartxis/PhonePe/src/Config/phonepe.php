<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PhonePe Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PhonePe payment gateway integration.
    | These values can be overridden in the admin panel settings.
    |
    */

    'phonepe' => [
        /*
        |--------------------------------------------------------------------------
        | Client ID
        |--------------------------------------------------------------------------
        |
        | Your PhonePe Client ID from the Business Dashboard.
        | This is required for API authentication.
        |
        */
        'client_id' => env('PHONEPE_CLIENT_ID', ''),

        /*
        |--------------------------------------------------------------------------
        | Client Secret
        |--------------------------------------------------------------------------
        |
        | Your PhonePe Client Secret from the Business Dashboard.
        | Keep this secure and never expose it publicly.
        |
        */
        'client_secret' => env('PHONEPE_CLIENT_SECRET', ''),

        /*
        |--------------------------------------------------------------------------
        | Client Version
        |--------------------------------------------------------------------------
        |
        | Your PhonePe Client Version from the Business Dashboard.
        | This is typically an integer value.
        |
        */
        'client_version' => env('PHONEPE_CLIENT_VERSION', 1),

        /*
        |--------------------------------------------------------------------------
        | Callback Username
        |--------------------------------------------------------------------------
        |
        | Username for Basic Authentication of webhook callbacks.
        | Must match the value configured in PhonePe Dashboard.
        |
        */
        'callback_username' => env('PHONEPE_CALLBACK_USERNAME', ''),

        /*
        |--------------------------------------------------------------------------
        | Callback Password
        |--------------------------------------------------------------------------
        |
        | Password for Basic Authentication of webhook callbacks.
        | Must match the value configured in PhonePe Dashboard.
        |
        */
        'callback_password' => env('PHONEPE_CALLBACK_PASSWORD', ''),

        /*
        |--------------------------------------------------------------------------
        | Environment
        |--------------------------------------------------------------------------
        |
        | The PhonePe environment to use.
        | The PHP SDK currently only supports PRODUCTION.
        |
        */
        'environment' => env('PHONEPE_ENVIRONMENT', 'PRODUCTION'),

        /*
        |--------------------------------------------------------------------------
        | Enable Event Publishing
        |--------------------------------------------------------------------------
        |
        | Set to true to enable event publishing for PhonePe analytics.
        | This helps PhonePe collect monitoring data.
        |
        */
        'publish_events' => env('PHONEPE_PUBLISH_EVENTS', false),

        /*
        |--------------------------------------------------------------------------
        | Supported Payment Methods
        |--------------------------------------------------------------------------
        |
        | Payment methods supported by PhonePe gateway.
        |
        */
        'payment_methods' => [
            'upi' => true,
            'card' => true,
            'netbanking' => true,
            'wallet' => true,
        ],
    ],
];
