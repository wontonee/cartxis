<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shop Package Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Shop package which handles
    | all storefront functionality including homepage, product browsing,
    | checkout flow, and customer account management.
    |
    */

    'name' => 'Shop',

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Configure route prefixes and middleware for shop routes.
    |
    */
    'routes' => [
        'prefix' => '',
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Homepage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure homepage settings and featured content.
    |
    */
    'homepage' => [
        'featured_products_count' => 12,
        'show_slider' => true,
        'show_categories' => true,
        'show_featured_products' => true,
        'show_new_products' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Listing Configuration
    |--------------------------------------------------------------------------
    |
    | Configure product listing page settings.
    |
    */
    'listing' => [
        'products_per_page' => 12,
        'available_limits' => [12, 24, 36, 48],
        'default_sort' => 'position',
        'available_sorts' => [
            'position' => 'Position',
            'name_asc' => 'Name: A to Z',
            'name_desc' => 'Name: Z to A',
            'price_asc' => 'Price: Low to High',
            'price_desc' => 'Price: High to Low',
            'created_at' => 'Newest First',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | Configure search functionality.
    |
    */
    'search' => [
        'min_query_length' => 3,
        'max_results' => 20,
        'enable_suggestions' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Customer Account Configuration
    |--------------------------------------------------------------------------
    |
    | Configure customer account settings.
    |
    */
    'account' => [
        'orders_per_page' => 10,
        'enable_wishlist' => true,
        'enable_compare' => true,
        'enable_reviews' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Checkout Configuration
    |--------------------------------------------------------------------------
    |
    | Configure checkout flow settings.
    |
    */
    'checkout' => [
        'allow_guest_checkout' => true,
        'require_terms_acceptance' => true,
        'enable_newsletter_signup' => true,
        'enable_order_notes' => true,
        'tax_rate' => 0.08, // 8% tax rate
        'free_shipping_threshold' => 100.00,
        'shipping_rates' => [
            'standard' => 5.00,
            'express' => 15.00,
            'overnight' => 25.00,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Configuration
    |--------------------------------------------------------------------------
    |
    | Configure order settings.
    |
    */
    'order' => [
        'number_prefix' => 'ORD',
        'auto_cancel_pending_after_days' => 7,
        'allow_cancellation_statuses' => ['pending', 'processing'],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    |
    | Configure SEO settings for shop pages.
    |
    */
    'seo' => [
        'enable_meta_tags' => true,
        'enable_schema_markup' => true,
        'enable_sitemap' => true,
        'enable_breadcrumbs' => true,
    ],
];
