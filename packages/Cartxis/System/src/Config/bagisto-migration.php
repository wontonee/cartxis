<?php

return [
    'connection' => env('BAGISTO_DB_CONNECTION', 'bagisto'),
    
    'database' => [
        'host' => env('BAGISTO_DB_HOST', '127.0.0.1'),
        'port' => env('BAGISTO_DB_PORT', '3306'),
        'database' => env('BAGISTO_DB_DATABASE', 'bagisto'),
        'username' => env('BAGISTO_DB_USERNAME', 'root'),
        'password' => env('BAGISTO_DB_PASSWORD', ''),
    ],
    
    'mappings' => [
        'customer_group' => [
            'Guest' => 'guest',
            'General' => 'general',
            'Wholesale' => 'wholesale',
        ],
        'product_type' => [
            'simple' => 'simple',
            'configurable' => 'configurable',
            'virtual' => 'virtual',
            'downloadable' => 'downloadable',
            'bundle' => 'bundle',
            'grouped' => 'grouped',
        ],
        'order_status' => [
            'pending' => 'pending',
            'processing' => 'processing',
            'completed' => 'completed',
            'canceled' => 'cancelled',
            'closed' => 'completed',
            'fraud' => 'failed',
            'pending_payment' => 'pending',
        ],
    ],
    
    'defaults' => [
        'channel_id' => 1,
        'locale_code' => 'en',
        'currency_code' => 'USD',
        'inventory_source_id' => 1,
    ],
    
    'media' => [
        'base_path' => env('BAGISTO_MEDIA_PATH', '/storage'),
        'copy_files' => env('BAGISTO_COPY_MEDIA', true),
    ],
    
    'batch_size' => 500,
];
