<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Active Theme
    |--------------------------------------------------------------------------
    |
    | The slug of the currently active theme. This is set dynamically by
    | ThemeServiceProvider during boot based on the database record.
    | Null means the default theme will be used.
    |
    */
    'active' => null,

    /*
    |--------------------------------------------------------------------------
    | Default Theme
    |--------------------------------------------------------------------------
    |
    | The slug of the default fallback theme. If the active theme doesn't
    | have a required view, the resolver will fall back to this theme.
    |
    */
    'default' => 'cartxis-default',

    /*
    |--------------------------------------------------------------------------
    | Themes Directory
    |--------------------------------------------------------------------------
    |
    | The base path where themes are stored. Each theme is a subdirectory
    | here, discovered by its theme.json file.
    |
    */
    'path' => base_path('themes'),
];
