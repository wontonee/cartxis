<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reports Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching settings for report data to improve performance.
    |
    */
    'cache' => [
        'enabled' => env('REPORTS_CACHE_ENABLED', true),
        'driver' => env('REPORTS_CACHE_DRIVER', 'redis'),
        'ttl' => [
            'sales' => env('REPORTS_CACHE_TTL_SALES', 900),        // 15 minutes
            'products' => env('REPORTS_CACHE_TTL_PRODUCTS', 900),  // 15 minutes
            'customers' => env('REPORTS_CACHE_TTL_CUSTOMERS', 1800), // 30 minutes
        ],
        'prefix' => 'vortex_reports_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Export Configuration
    |--------------------------------------------------------------------------
    |
    | Configure export formats and settings.
    |
    */
    'export' => [
        'formats' => ['pdf', 'excel', 'csv'],
        'pdf_engine' => env('REPORTS_PDF_ENGINE', 'dompdf'), // dompdf or snappy
        'max_records' => env('REPORTS_EXPORT_MAX_RECORDS', 10000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Chart Configuration
    |--------------------------------------------------------------------------
    |
    | Default chart settings.
    |
    */
    'charts' => [
        'colors' => [
            'primary' => '#3b82f6',
            'success' => '#10b981',
            'warning' => '#f59e0b',
            'danger' => '#ef4444',
            'info' => '#06b6d4',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Date Range Presets
    |--------------------------------------------------------------------------
    |
    | Predefined date ranges for quick filtering.
    |
    */
    'date_presets' => [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'last_7_days' => 'Last 7 Days',
        'last_30_days' => 'Last 30 Days',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'this_year' => 'This Year',
        'custom' => 'Custom Range',
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Settings to optimize report performance.
    |
    */
    'performance' => [
        'chunk_size' => 1000, // For large data exports
        'query_timeout' => 30, // seconds
    ],
];
