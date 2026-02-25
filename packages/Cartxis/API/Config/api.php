<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Cartxis API endpoints used by mobile applications.
    |
    */

    // API Version
    'version' => 'v1',

    // Rate Limiting
    'rate_limits' => [
        'guest' => 60,           // Requests per minute for guests
        'authenticated' => 300,   // Requests per minute for authenticated users
        'payment' => 10,          // Requests per minute for payment endpoints
        'login' => 5,             // Login attempts per minute
    ],

    // Token Configuration
    'token' => [
        'expiration' => 60 * 24,  // Token expiration in minutes (24 hours)
        'name' => 'mobile-app',   // Default token name
    ],

    // Pagination
    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
    ],

    // Response Configuration
    'response' => [
        'include_meta' => true,
        'include_timestamp' => true,
        'include_version' => true,
    ],

    // Feature Flags
    'features' => [
        'wishlist' => true,
        'reviews' => true,
        'coupons' => true,
        'gift_cards' => false,
        'loyalty_points' => false,
    ],

    // Payment Gateways
    'payment_gateways' => [
        'stripe' => env('STRIPE_ENABLED', true),
        'razorpay' => env('RAZORPAY_ENABLED', true),
        'cod' => env('COD_ENABLED', true),
    ],

    // File Upload Limits
    'uploads' => [
        'avatar' => [
            'max_size' => 2048, // KB
            'allowed_types' => ['jpg', 'jpeg', 'png', 'gif'],
        ],
        'review_images' => [
            'max_size' => 5120, // KB
            'max_count' => 5,
            'allowed_types' => ['jpg', 'jpeg', 'png'],
        ],
    ],

    // Search Configuration
    'search' => [
        'min_query_length' => 2,
        'suggestions_limit' => 10,
        'results_per_page' => 20,
    ],

    // Cache Configuration
    'cache' => [
        'enabled' => env('API_CACHE_ENABLED', true),
        'ttl' => [
            'products' => 3600,      // 1 hour
            'categories' => 86400,   // 24 hours
            'settings' => 86400,     // 24 hours
        ],
    ],
];
