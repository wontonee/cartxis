<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Razorpay Configuration
    |--------------------------------------------------------------------------
    |
    | Razorpay payment gateway configuration.
    | Get your API keys from: https://dashboard.razorpay.com/app/keys
    |
    */
    
    'razorpay' => [
        
        /*
        |--------------------------------------------------------------------------
        | API Keys
        |--------------------------------------------------------------------------
        |
        | Your Razorpay Key ID and Key Secret.
        | These can be found in your Razorpay Dashboard under Settings > API Keys.
        |
        */
        
        'key_id' => env('RAZORPAY_KEY_ID', ''),
        'key_secret' => env('RAZORPAY_KEY_SECRET', ''),
        
        /*
        |--------------------------------------------------------------------------
        | Currency
        |--------------------------------------------------------------------------
        |
        | The default currency for Razorpay transactions.
        | Razorpay primarily supports INR (Indian Rupee).
        |
        */
        
        'currency' => env('RAZORPAY_CURRENCY', 'INR'),
        
        /*
        |--------------------------------------------------------------------------
        | Webhook Secret
        |--------------------------------------------------------------------------
        |
        | Secret used to verify Razorpay webhook signatures.
        | This can be found in your Razorpay Dashboard under Settings > Webhooks.
        |
        */
        
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),
        
        /*
        |--------------------------------------------------------------------------
        | Payment Capture
        |--------------------------------------------------------------------------
        |
        | Determines if payments should be captured automatically or manually.
        | true = auto-capture, false = manual capture
        |
        */
        
        'auto_capture' => env('RAZORPAY_AUTO_CAPTURE', true),
        
        /*
        |--------------------------------------------------------------------------
        | Payment Methods
        |--------------------------------------------------------------------------
        |
        | Payment methods to enable. By default, all methods are enabled.
        | Options: card, netbanking, wallet, emi, upi
        |
        */
        
        'payment_methods' => [
            'card' => true,
            'netbanking' => true,
            'wallet' => true,
            'emi' => false,
            'upi' => true,
        ],
        
    ],
];
