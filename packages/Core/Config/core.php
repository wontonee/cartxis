<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Extension Directory
    |--------------------------------------------------------------------------
    |
    | This value determines the directory where extensions are stored.
    |
    */
    'extensions_path' => base_path('extensions'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for settings and other core features.
    |
    */
    'cache' => [
        'enabled' => true,
        'duration' => 3600, // 1 hour in seconds
        'prefix' => 'vortex:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Locations
    |--------------------------------------------------------------------------
    |
    | Define the available menu locations in your application.
    |
    */
    'menu_locations' => [
        'admin' => 'Admin Panel Menu',
        'storefront' => 'Storefront Menu',
        'customer_account' => 'Customer Account Menu',
    ],

    /*
    |--------------------------------------------------------------------------
    | Icon Libraries
    |--------------------------------------------------------------------------
    |
    | Configure which icon libraries are available for use.
    |
    */
    'icon_libraries' => [
        'keenicons' => 'Keenicons',
        'heroicons' => 'Heroicons',
        'fontawesome' => 'Font Awesome',
        'custom' => 'Custom Icons',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Groups
    |--------------------------------------------------------------------------
    |
    | Define permission groups for better organization.
    |
    */
    'permission_groups' => [
        'dashboard' => 'Dashboard',
        'catalog' => 'Catalog Management',
        'sales' => 'Sales Management',
        'customers' => 'Customer Management',
        'marketing' => 'Marketing',
        'cms' => 'Content Management',
        'extensions' => 'Extensions',
        'settings' => 'Settings',
        'reports' => 'Reports',
    ],
];
