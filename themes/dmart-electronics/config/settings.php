<?php

return [
    'sections' => [
        // Colors
        [
            'id' => 'colors',
            'title' => 'Colors',
            'description' => 'Customize the Dmart Electronics color scheme',
            'fields' => [
                ['id' => 'primary_color', 'type' => 'color', 'label' => 'Primary Color', 'default' => '#FF4035'],
                ['id' => 'secondary_color', 'type' => 'color', 'label' => 'Dark / Title Color', 'default' => '#0A111E'],
                ['id' => 'accent_color', 'type' => 'color', 'label' => 'Accent Color', 'default' => '#FFD43B'],
                ['id' => 'success_color', 'type' => 'color', 'label' => 'Success Color', 'default' => '#10b981'],
                ['id' => 'danger_color', 'type' => 'color', 'label' => 'Danger Color', 'default' => '#ef4444'],
            ],
        ],
        // Typography
        [
            'id' => 'typography',
            'title' => 'Typography',
            'description' => 'Font settings for the theme',
            'fields' => [
                [
                    'id' => 'body_font', 'type' => 'select', 'label' => 'Body Font',
                    'options' => [
                        "'Jost', sans-serif" => 'Jost',
                        "'Inter', sans-serif" => 'Inter',
                        "'Roboto', sans-serif" => 'Roboto',
                        "'Open Sans', sans-serif" => 'Open Sans',
                    ],
                    'default' => "'Jost', sans-serif",
                ],
                [
                    'id' => 'heading_font', 'type' => 'select', 'label' => 'Heading Font',
                    'options' => [
                        "'Albert Sans', sans-serif" => 'Albert Sans',
                        "'Inter', sans-serif" => 'Inter',
                        "'Poppins', sans-serif" => 'Poppins',
                        "'Montserrat', sans-serif" => 'Montserrat',
                    ],
                    'default' => "'Albert Sans', sans-serif",
                ],
                ['id' => 'font_size', 'type' => 'number', 'label' => 'Base Font Size (px)', 'default' => 16, 'min' => 12, 'max' => 24, 'step' => 1],
            ],
        ],
        // Layout
        [
            'id' => 'layout',
            'title' => 'Layout',
            'description' => 'Layout configuration',
            'fields' => [
                [
                    'id' => 'container_width', 'type' => 'select', 'label' => 'Container Width',
                    'options' => ['1800px' => 'Default (1800px)', '1536px' => 'Wide (1536px)', '1320px' => 'Normal (1320px)', '1140px' => 'Compact (1140px)', '100%' => 'Full Width'],
                    'default' => '1800px',
                ],
                [
                    'id' => 'products_per_row', 'type' => 'select', 'label' => 'Products Per Row',
                    'options' => ['3' => '3 Columns', '4' => '4 Columns', '5' => '5 Columns'],
                    'default' => '4',
                ],
                [
                    'id' => 'sidebar_position', 'type' => 'select', 'label' => 'Shop Sidebar',
                    'options' => ['left' => 'Left Sidebar', 'right' => 'Right Sidebar', 'none' => 'No Sidebar'],
                    'default' => 'left',
                ],
            ],
        ],
        // Features
        [
            'id' => 'features',
            'title' => 'Features',
            'description' => 'Toggle theme features',
            'fields' => [
                ['id' => 'sticky_header', 'type' => 'boolean', 'label' => 'Sticky Header', 'default' => true],
                ['id' => 'back_to_top', 'type' => 'boolean', 'label' => 'Back to Top Button', 'default' => true],
                ['id' => 'wishlist', 'type' => 'boolean', 'label' => 'Wishlist', 'default' => true],
                ['id' => 'quick_view', 'type' => 'boolean', 'label' => 'Quick View', 'default' => true],
                ['id' => 'product_zoom', 'type' => 'boolean', 'label' => 'Product Image Zoom', 'default' => true],
                ['id' => 'offer_marquee', 'type' => 'boolean', 'label' => 'Offer Marquee Banner', 'default' => true],
                ['id' => 'newsletter_popup', 'type' => 'boolean', 'label' => 'Newsletter Popup', 'default' => false],
            ],
        ],
        // Contact Information
        [
            'id' => 'contact',
            'title' => 'Contact Information',
            'description' => 'Contact details displayed in the footer and contact pages',
            'fields' => [
                ['id' => 'phone', 'type' => 'text', 'label' => 'Phone Number', 'default' => '+208-555-0112'],
                ['id' => 'email', 'type' => 'text', 'label' => 'Email Address', 'default' => 'example@gmail.com'],
                ['id' => 'hours', 'type' => 'text', 'label' => 'Opening Hours', 'default' => 'Sunday - Fri: 9 AM - 6 PM'],
                ['id' => 'address', 'type' => 'text', 'label' => 'Address', 'default' => '4517 Washington Ave.'],
            ],
        ],
        // Social Media
        [
            'id' => 'social',
            'title' => 'Social Media',
            'description' => 'Social media profile links displayed in the footer',
            'fields' => [
                ['id' => 'facebook', 'type' => 'text', 'label' => 'Facebook URL', 'default' => '#'],
                ['id' => 'twitter', 'type' => 'text', 'label' => 'Twitter / X URL', 'default' => '#'],
                ['id' => 'instagram', 'type' => 'text', 'label' => 'Instagram URL', 'default' => '#'],
                ['id' => 'linkedin', 'type' => 'text', 'label' => 'LinkedIn URL', 'default' => '#'],
                ['id' => 'youtube', 'type' => 'text', 'label' => 'YouTube URL', 'default' => ''],
            ],
        ],
        // Homepage
        [
            'id' => 'homepage',
            'title' => 'Homepage',
            'description' => 'Homepage section visibility',
            'fields' => [
                ['id' => 'show_hero', 'type' => 'boolean', 'label' => 'Hero Slider', 'default' => true],
                ['id' => 'show_categories', 'type' => 'boolean', 'label' => 'Popular Categories', 'default' => true],
                ['id' => 'show_best_sellers', 'type' => 'boolean', 'label' => 'Best Sellers', 'default' => true],
                ['id' => 'show_featured', 'type' => 'boolean', 'label' => 'Featured Products', 'default' => true],
                ['id' => 'show_testimonials', 'type' => 'boolean', 'label' => 'Testimonials', 'default' => true],
                ['id' => 'show_blog', 'type' => 'boolean', 'label' => 'Blog Section', 'default' => true],
            ],
        ],
    ],
];
