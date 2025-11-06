<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Page Cache TTL
    |--------------------------------------------------------------------------
    |
    | The time to live (in seconds) for cached pages.
    | Default: 3600 (1 hour)
    |
    */
    'page_cache_ttl' => env('CMS_PAGE_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Max Pages Per Query
    |--------------------------------------------------------------------------
    |
    | The maximum number of pages to return per query.
    | Default: 100
    |
    */
    'max_pages_per_query' => 100,

    /*
    |--------------------------------------------------------------------------
    | Allowed HTML Tags
    |--------------------------------------------------------------------------
    |
    | HTML tags allowed in page content for XSS protection.
    | Uses HTML Purifier.
    |
    */
    'allowed_html_tags' => [
        'p', 'br', 'strong', 'em', 'u', 's', 'strike',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'a[href|title|target]',
        'img[src|alt|title|width|height]',
        'blockquote', 'pre', 'code',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'div[class]', 'span[class]',
        'iframe[src|width|height|frameborder|allowfullscreen]',
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable Content Sanitization
    |--------------------------------------------------------------------------
    |
    | Whether to sanitize HTML content using HTML Purifier.
    | Recommended: true for security
    |
    */
    'sanitize_content' => env('CMS_SANITIZE_CONTENT', true),

    /*
    |--------------------------------------------------------------------------
    | Enable Auto-Save Drafts
    |--------------------------------------------------------------------------
    |
    | Enable automatic draft saving in the admin editor.
    | Interval is defined in frontend (default: 30 seconds)
    |
    */
    'enable_auto_save' => true,

    /*
    |--------------------------------------------------------------------------
    | Media Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for media library uploads.
    |
    */
    'media' => [
        'disk' => env('CMS_MEDIA_DISK', 'public'),
        'max_file_size' => env('CMS_MAX_FILE_SIZE', 10240), // 10MB in kilobytes
        'allowed_mime_types' => [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/svg+xml',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'video/mp4',
            'video/mpeg',
            'video/quicktime',
        ],
        'thumbnail_sizes' => [150, 600, 1200],
        'optimize_images' => true,
    ],

];
