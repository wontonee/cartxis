<?php

return [
    'connection' => env('WOOCOMMERCE_DB_CONNECTION', 'woocommerce'),
    
    'database' => [
        'host' => env('WOOCOMMERCE_DB_HOST', '127.0.0.1'),
        'port' => env('WOOCOMMERCE_DB_PORT', '3306'),
        'database' => env('WOOCOMMERCE_DB_DATABASE', 'wordpress'),
        'username' => env('WOOCOMMERCE_DB_USERNAME', 'root'),
        'password' => env('WOOCOMMERCE_DB_PASSWORD', ''),
        'prefix' => env('WOOCOMMERCE_DB_PREFIX', 'wp_'),
    ],
    
    'hpos_enabled' => env('WOOCOMMERCE_HPOS_ENABLED', false),
    
    'mappings' => [
        'product_status' => [
            'publish' => 'active',
            'draft' => 'draft',
            'pending' => 'draft',
            'private' => 'inactive',
        ],
        'order_status' => [
            'wc-pending' => 'pending',
            'wc-processing' => 'processing',
            'wc-on-hold' => 'pending',
            'wc-completed' => 'completed',
            'wc-cancelled' => 'cancelled',
            'wc-refunded' => 'refunded',
            'wc-failed' => 'failed',
        ],
    ],
    
    'defaults' => [
        'store_id' => 1,
        'locale' => 'en',
        'currency' => 'USD',
    ],
    
    'media' => [
        'base_path' => env('WOOCOMMERCE_MEDIA_PATH', '/wp-content/uploads'),
        'copy_files' => env('WOOCOMMERCE_COPY_MEDIA', true),
    ],
    
    'batch_size' => 500,
];
