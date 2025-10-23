<?php

return [
    /**
     * Stripe Configuration
     */
    'stripe' => [
        /**
         * API Keys
         */
        'public_key' => env('STRIPE_PUBLIC_KEY', ''),
        'secret_key' => env('STRIPE_SECRET_KEY', ''),
        
        /**
         * Payment Settings
         */
        'currency' => env('STRIPE_CURRENCY', 'usd'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
        'api_version' => env('STRIPE_API_VERSION', '2023-10-16'),
        
        /**
         * Features
         */
        'enable_3d_secure' => env('STRIPE_ENABLE_3D_SECURE', true),
        'save_payment_method' => env('STRIPE_SAVE_PAYMENT_METHOD', true),
        'allow_existing_payment_methods' => env('STRIPE_ALLOW_EXISTING_METHODS', true),
    ],

    /**
     * Payment Intent Settings
     */
    'payment_intent' => [
        'confirmation_method' => 'automatic',
        'setup_future_usage' => 'off_session', // Store payment method for future use
    ],

    /**
     * Webhook Configuration
     */
    'webhooks' => [
        'events' => [
            'payment_intent.succeeded',
            'payment_intent.payment_failed',
            'payment_intent.canceled',
            'charge.refunded',
        ],
    ],

    /**
     * Fee Configuration
     */
    'fees' => [
        'enable_fees' => false,
        'percentage' => 2.9, // 2.9%
        'fixed_amount' => 30, // 0.30 in minor units
    ],
];
