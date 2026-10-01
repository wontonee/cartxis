<?php

namespace Cartxis\Cart\Support;

use Cartxis\Product\Models\Product;

class CartTypeHelper
{
    /**
     * Build cart line type metadata from a product.
     *
     * @return array{type: string, requires_shipping: bool}
     */
    public static function snapshotFromProduct(Product $product): array
    {
        return [
            'type' => (string) ($product->type ?? Product::TYPE_SIMPLE),
            'requires_shipping' => $product->requiresShipping(),
        ];
    }

    /**
     * Whether a cart line requires shipping.
     */
    public static function lineRequiresShipping(array $item): bool
    {
        if (array_key_exists('requires_shipping', $item)) {
            return (bool) $item['requires_shipping'];
        }

        $type = $item['type'] ?? Product::TYPE_SIMPLE;

        return in_array($type, [Product::TYPE_SIMPLE, Product::TYPE_CONFIGURABLE], true);
    }

    /**
     * Whether the cart has any physical (shippable) lines.
     */
    public static function cartRequiresShipping(array $items): bool
    {
        foreach ($items as $item) {
            if (self::lineRequiresShipping(is_array($item) ? $item : (array) $item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether every cart line is digital-only (virtual/downloadable).
     */
    public static function isDigitalOnly(array $items): bool
    {
        if (empty($items)) {
            return false;
        }

        return ! self::cartRequiresShipping($items);
    }

    /**
     * Whether the product may be added to the shopping cart.
     */
    public static function canAddToCart(Product $product): bool
    {
        return ! $product->isQuote();
    }

    /**
     * Check stock when manage_stock is enabled.
     */
    public static function hasInsufficientStock(Product $product, int $quantity): bool
    {
        if (! $product->manage_stock) {
            return false;
        }

        return (int) $product->quantity < $quantity;
    }
}
