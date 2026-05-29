<?php

/**
 * Cartxis Default Theme - Hooks & Filters
 * 
 * This file is loaded when the theme is active.
 * Register your custom hooks and filters here.
 */

// Modify product card data - Add "New" badge for recent products
add_filter('catalog.product.card.data', function ($product) {
    if (isset($product['created_at'])) {
        $createdDate = new \Carbon\Carbon($product['created_at']);
        $daysSinceCreation = now()->diffInDays($createdDate);
        
        if ($daysSinceCreation <= 30) {
            $product['badges'] = $product['badges'] ?? [];
            $product['badges'][] = [
                'text' => 'New',
                'class' => 'bg-blue-500 text-white text-xs px-2 py-1 rounded',
            ];
        }
    }
    
    // Add "Hot" badge for products with high sales
    if (isset($product['sales_count']) && $product['sales_count'] > 100) {
        $product['badges'] = $product['badges'] ?? [];
        $product['badges'][] = [
            'text' => 'Hot',
            'class' => 'bg-red-500 text-white text-xs px-2 py-1 rounded',
        ];
    }
    
    return $product;
}, 10, 1);

// Modify cart total display format
add_filter('cart.total.display', function ($total) {
    return '$' . number_format($total, 2);
}, 10, 1);

// Add custom message after checkout
add_action('checkout.order.placed', function ($order) {
    // You can add custom logic here
    \Log::info("Order #{$order->id} placed successfully");
}, 10, 1);

// Modify product price display
add_filter('catalog.product.price', function ($price, $product) {
    // Apply any custom pricing logic
    return $price;
}, 10, 2);
