<?php

/**
 * Vortex Default Theme Settings Schema
 * 
 * Define the settings fields that will appear in the theme customizer
 */

return [
    'sections' => [
        // Colors Section
        [
            'id' => 'colors',
            'title' => 'Colors',
            'description' => 'Customize the color scheme of your store',
            'fields' => [
                [
                    'id' => 'primary_color',
                    'type' => 'color',
                    'label' => 'Primary Color',
                    'description' => 'Main brand color used for buttons, links, etc.',
                    'default' => '#3b82f6',
                ],
                [
                    'id' => 'secondary_color',
                    'type' => 'color',
                    'label' => 'Secondary Color',
                    'description' => 'Secondary color for accents',
                    'default' => '#8b5cf6',
                ],
                [
                    'id' => 'accent_color',
                    'type' => 'color',
                    'label' => 'Accent Color',
                    'description' => 'Used for highlights and special elements',
                    'default' => '#f59e0b',
                ],
                [
                    'id' => 'success_color',
                    'type' => 'color',
                    'label' => 'Success Color',
                    'default' => '#10b981',
                ],
                [
                    'id' => 'danger_color',
                    'type' => 'color',
                    'label' => 'Danger Color',
                    'default' => '#ef4444',
                ],
            ],
        ],
        
        // Typography Section
        [
            'id' => 'typography',
            'title' => 'Typography',
            'description' => 'Customize fonts and text styles',
            'fields' => [
                [
                    'id' => 'font_family',
                    'type' => 'select',
                    'label' => 'Body Font Family',
                    'options' => [
                        'Inter, sans-serif' => 'Inter',
                        'Roboto, sans-serif' => 'Roboto',
                        'Open Sans, sans-serif' => 'Open Sans',
                        'Lato, sans-serif' => 'Lato',
                        'Poppins, sans-serif' => 'Poppins',
                        'Montserrat, sans-serif' => 'Montserrat',
                    ],
                    'default' => 'Inter, sans-serif',
                ],
                [
                    'id' => 'heading_font',
                    'type' => 'select',
                    'label' => 'Heading Font Family',
                    'options' => [
                        'Inter, sans-serif' => 'Inter',
                        'Roboto, sans-serif' => 'Roboto',
                        'Open Sans, sans-serif' => 'Open Sans',
                        'Lato, sans-serif' => 'Lato',
                        'Poppins, sans-serif' => 'Poppins',
                        'Montserrat, sans-serif' => 'Montserrat',
                    ],
                    'default' => 'Inter, sans-serif',
                ],
                [
                    'id' => 'font_size',
                    'type' => 'number',
                    'label' => 'Base Font Size (px)',
                    'default' => 16,
                    'min' => 12,
                    'max' => 24,
                    'step' => 1,
                ],
            ],
        ],
        
        // Layout Section
        [
            'id' => 'layout',
            'title' => 'Layout',
            'description' => 'Control the layout and spacing',
            'fields' => [
                [
                    'id' => 'container_width',
                    'type' => 'select',
                    'label' => 'Container Width',
                    'options' => [
                        '1140px' => 'Normal (1140px)',
                        '1280px' => 'Wide (1280px)',
                        '1536px' => 'Extra Wide (1536px)',
                        '100%' => 'Full Width',
                    ],
                    'default' => '1280px',
                ],
                [
                    'id' => 'header_style',
                    'type' => 'select',
                    'label' => 'Header Style',
                    'options' => [
                        'default' => 'Default',
                        'centered' => 'Centered',
                        'minimal' => 'Minimal',
                        'sticky' => 'Sticky',
                    ],
                    'default' => 'default',
                ],
                [
                    'id' => 'sidebar_position',
                    'type' => 'select',
                    'label' => 'Sidebar Position',
                    'options' => [
                        'left' => 'Left',
                        'right' => 'Right',
                    ],
                    'default' => 'left',
                ],
            ],
        ],
        
        // Features Section
        [
            'id' => 'features',
            'title' => 'Features',
            'description' => 'Enable or disable theme features',
            'fields' => [
                [
                    'id' => 'sticky_header',
                    'type' => 'boolean',
                    'label' => 'Sticky Header',
                    'description' => 'Keep header fixed at top when scrolling',
                    'default' => true,
                ],
                [
                    'id' => 'back_to_top',
                    'type' => 'boolean',
                    'label' => 'Back to Top Button',
                    'description' => 'Show scroll to top button',
                    'default' => true,
                ],
                [
                    'id' => 'wishlist',
                    'type' => 'boolean',
                    'label' => 'Wishlist',
                    'description' => 'Enable wishlist functionality',
                    'default' => true,
                ],
                [
                    'id' => 'quick_view',
                    'type' => 'boolean',
                    'label' => 'Quick View',
                    'description' => 'Enable product quick view modal',
                    'default' => true,
                ],
                [
                    'id' => 'product_zoom',
                    'type' => 'boolean',
                    'label' => 'Product Image Zoom',
                    'description' => 'Enable zoom on product images',
                    'default' => true,
                ],
            ],
        ],
    ],
];
