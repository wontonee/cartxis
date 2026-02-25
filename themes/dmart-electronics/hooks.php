<?php

/**
 * Dmart Electronics Theme - Hooks & Filters
 *
 * This file is loaded when the theme is active.
 * Register your custom hooks and filters here.
 */

// ─── Theme Shared Data ───────────────────────────────────────────────
// Register additional Inertia shared props that THIS theme needs.
// These are only shared when dmart-electronics is the active theme.
// Other themes (like cartxis-default) are not affected.
add_filter('theme.shared_data', function ($data) {
    $data['contactInfo'] = function () {
        try {
            $theme = \Cartxis\Core\Models\Theme::where('is_active', true)->first();
            if ($theme) {
                return [
                    'phone' => $theme->getSetting('contact.phone') ?? '+208-555-0112',
                    'email' => $theme->getSetting('contact.email') ?? 'example@gmail.com',
                    'hours' => $theme->getSetting('contact.hours') ?? 'Sunday - Fri: 9 AM - 6 PM',
                    'address' => $theme->getSetting('contact.address') ?? '4517 Washington Ave.',
                ];
            }
        } catch (\Exception $e) {}
        return null;
    };

    $data['socialLinks'] = function () {
        try {
            $theme = \Cartxis\Core\Models\Theme::where('is_active', true)->first();
            if ($theme) {
                return [
                    'facebook' => $theme->getSetting('social.facebook') ?? '#',
                    'twitter' => $theme->getSetting('social.twitter') ?? '#',
                    'instagram' => $theme->getSetting('social.instagram') ?? '#',
                    'linkedin' => $theme->getSetting('social.linkedin') ?? '#',
                    'youtube' => $theme->getSetting('social.youtube') ?? '',
                ];
            }
        } catch (\Exception $e) {}
        return null;
    };

    $data['footerNewsletter'] = function () {
        try {
            $block = \Cartxis\CMS\Models\Block::where('identifier', 'footer-newsletter')
                ->where('status', 'active')
                ->first();

            if (!$block) {
                return null;
            }

            $content = $block->content;
            if (is_string($content)) {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $content = $decoded;
                }
            }

            return [
                'heading' => $content['heading'] ?? $block->title ?? 'Newsletter',
                'description' => $content['description'] ?? 'Sign up to searing weekly newsletter to get the latest updates.',
                'placeholder' => $content['placeholder'] ?? 'Enter Email Address',
                'button_text' => $content['button_text'] ?? 'Subscribe',
            ];
        } catch (\Exception $e) {
            return null;
        }
    };

    return $data;
}, 10, 1);

// ─── Product Card Badges ─────────────────────────────────────────────
// Add "New" badge for products created in the last 14 days
add_filter('catalog.product.card.data', function ($product) {
    if (isset($product['created_at'])) {
        $createdDate = new \Carbon\Carbon($product['created_at']);
        $daysSinceCreation = now()->diffInDays($createdDate);

        if ($daysSinceCreation <= 14) {
            $product['badges'] = $product['badges'] ?? [];
            $product['badges'][] = [
                'text' => 'New',
                'class' => 'badge-new',
            ];
        }
    }

    // Add "Hot" badge for products with high sales
    if (isset($product['sales_count']) && $product['sales_count'] > 50) {
        $product['badges'] = $product['badges'] ?? [];
        $product['badges'][] = [
            'text' => 'Hot',
            'class' => 'badge-hot',
        ];
    }

    return $product;
}, 10, 1);

// Set products per page based on theme setting
add_filter('theme.products_per_page', function ($perPage) {
    return theme_setting('layout.products_per_row', 4) * 4;
}, 10, 1);

// Add theme body classes
add_filter('theme.body_classes', function ($classes) {
    $classes[] = 'theme-dmart-electronics';
    if (theme_setting('features.sticky_header', true)) {
        $classes[] = 'has-sticky-header';
    }
    return $classes;
}, 10, 1);

// Modify cart total display format
add_filter('cart.total.display', function ($total) {
    return '$' . number_format($total, 2);
}, 10, 1);
