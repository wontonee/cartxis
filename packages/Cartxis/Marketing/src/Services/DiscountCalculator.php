<?php

namespace Cartxis\Marketing\Services;

use Cartxis\Marketing\Models\Coupon;
use Cartxis\Marketing\Models\Promotion;
use Illuminate\Support\Collection;

/**
 * DiscountCalculator
 * 
 * Handles all discount calculations for coupons and promotions.
 */
class DiscountCalculator
{
    /**
     * Calculate discount amount for a coupon.
     */
    public function calculateCouponDiscount(Coupon $coupon, float $subtotal, Collection $items): float
    {
        return match($coupon->type) {
            Coupon::TYPE_PERCENTAGE => $this->calculatePercentageDiscount($coupon, $subtotal, $items),
            Coupon::TYPE_FIXED_AMOUNT => $this->calculateFixedAmountDiscount($coupon, $subtotal),
            Coupon::TYPE_FREE_SHIPPING => 0, // Handled separately in shipping calculation
            Coupon::TYPE_BUY_X_GET_Y => $this->calculateBuyXGetYDiscount($coupon, $items),
            Coupon::TYPE_FIXED_PRICE => $this->calculateFixedPriceDiscount($coupon, $items),
            default => 0,
        };
    }

    /**
     * Calculate percentage discount.
     */
    protected function calculatePercentageDiscount(Coupon $coupon, float $subtotal, Collection $items): float
    {
        // Get eligible amount (respecting product/category restrictions)
        $eligibleAmount = $this->getEligibleAmount($coupon, $items);
        
        if ($eligibleAmount <= 0) {
            return 0;
        }

        // Calculate percentage discount
        $discount = ($eligibleAmount * $coupon->value) / 100;

        // Apply max discount cap if set
        if ($coupon->max_discount && $discount > $coupon->max_discount) {
            $discount = $coupon->max_discount;
        }

        return round($discount, 2);
    }

    /**
     * Calculate fixed amount discount.
     */
    protected function calculateFixedAmountDiscount(Coupon $coupon, float $subtotal): float
    {
        // Fixed discount cannot exceed subtotal
        return min($coupon->value, $subtotal);
    }

    /**
     * Calculate Buy X Get Y discount.
     */
    protected function calculateBuyXGetYDiscount(Coupon $coupon, Collection $items): float
    {
        $discount = 0;

        // Filter items to only buy_products
        $buyProductIds = $coupon->buy_products ?? [];
        $getProductIds = $coupon->get_products ?? [];

        if (empty($buyProductIds) || empty($getProductIds)) {
            return 0;
        }

        // Count items in buy group
        $buyQuantity = $items
            ->whereIn('product_id', $buyProductIds)
            ->sum('quantity');

        // Check if customer qualifies
        if ($buyQuantity < $coupon->buy_quantity) {
            return 0;
        }

        // Calculate how many free items customer gets
        $freeItemSets = floor($buyQuantity / $coupon->buy_quantity);
        $freeItemsCount = $freeItemSets * $coupon->get_quantity;

        // Get cheapest items from get_products to discount
        $getItems = $items
            ->whereIn('product_id', $getProductIds)
            ->sortBy('price')
            ->take($freeItemsCount);

        foreach ($getItems as $item) {
            $qtyToDiscount = min($item->quantity, $freeItemsCount);
            $discount += $item->price * $qtyToDiscount;
            $freeItemsCount -= $qtyToDiscount;

            if ($freeItemsCount <= 0) {
                break;
            }
        }

        return round($discount, 2);
    }

    /**
     * Calculate fixed price discount.
     */
    protected function calculateFixedPriceDiscount(Coupon $coupon, Collection $items): float
    {
        $discount = 0;
        $applicableProductIds = $coupon->applicable_products ?? [];

        if (empty($applicableProductIds)) {
            return 0;
        }

        // Apply fixed price to applicable products
        foreach ($items as $item) {
            if (in_array($item->product_id, $applicableProductIds)) {
                $currentPrice = $item->price;
                $fixedPrice = $coupon->value;

                if ($fixedPrice < $currentPrice) {
                    $discount += ($currentPrice - $fixedPrice) * $item->quantity;
                }
            }
        }

        return round($discount, 2);
    }

    /**
     * Get eligible amount respecting product/category restrictions.
     */
    protected function getEligibleAmount(Coupon $coupon, Collection $items): float
    {
        $eligibleAmount = 0;

        foreach ($items as $item) {
            if ($this->isItemEligibleForCoupon($coupon, $item)) {
                $eligibleAmount += $item->price * $item->quantity;
            }
        }

        return $eligibleAmount;
    }

    /**
     * Check if cart item is eligible for coupon.
     */
    protected function isItemEligibleForCoupon(Coupon $coupon, $item): bool
    {
        // Check if on sale and excluded
        if ($coupon->exclude_sale_items && ($item->is_on_sale ?? false)) {
            return false;
        }

        // Check excluded products
        if ($coupon->excluded_products && in_array($item->product_id, $coupon->excluded_products)) {
            return false;
        }

        // Check excluded categories
        if ($coupon->excluded_categories && $item->categories) {
            $itemCategoryIds = is_array($item->categories) 
                ? collect($item->categories)->pluck('id')->toArray()
                : [$item->category_id ?? null];

            if (array_intersect($coupon->excluded_categories, $itemCategoryIds)) {
                return false;
            }
        }

        // If applicable products specified, item must be in list
        if ($coupon->applicable_products) {
            if (!in_array($item->product_id, $coupon->applicable_products)) {
                return false;
            }
        }

        // If applicable categories specified, item must be in one
        if ($coupon->applicable_categories) {
            $itemCategoryIds = is_array($item->categories) 
                ? collect($item->categories)->pluck('id')->toArray()
                : [$item->category_id ?? null];

            if (!array_intersect($coupon->applicable_categories, $itemCategoryIds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate discount amount for a promotion.
     */
    public function calculatePromotionDiscount(Promotion $promotion, float $subtotal, Collection $items): float
    {
        if ($promotion->discount_type === Promotion::DISCOUNT_PERCENTAGE) {
            $discount = ($subtotal * $promotion->discount_value) / 100;

            if ($promotion->max_discount && $discount > $promotion->max_discount) {
                $discount = $promotion->max_discount;
            }

            return round($discount, 2);
        }

        // Fixed amount
        return min($promotion->discount_value, $subtotal);
    }

    /**
     * Calculate promotional price for a product (catalog rules).
     */
    public function calculatePromotionalPrice(float $originalPrice, Promotion $promotion): float
    {
        if ($promotion->discount_type === Promotion::DISCOUNT_PERCENTAGE) {
            $discount = ($originalPrice * $promotion->discount_value) / 100;
            return round($originalPrice - $discount, 2);
        }

        // Fixed amount
        $newPrice = $originalPrice - $promotion->discount_value;
        return round(max(0, $newPrice), 2);
    }

    /**
     * Calculate tiered pricing discount.
     */
    public function calculateTieredPriceDiscount(Promotion $promotion, float $basePrice, int $quantity): float
    {
        if (!$promotion->price_tiers) {
            return 0;
        }

        $applicableTier = null;

        foreach ($promotion->price_tiers as $tier) {
            $minQty = $tier['min_quantity'] ?? 0;
            $maxQty = $tier['max_quantity'] ?? PHP_INT_MAX;

            if ($quantity >= $minQty && $quantity <= $maxQty) {
                $applicableTier = $tier;
                break;
            }
        }

        if (!$applicableTier) {
            return 0;
        }

        $discountPercentage = $applicableTier['discount_percentage'] ?? 0;
        $discountPerUnit = ($basePrice * $discountPercentage) / 100;
        $totalDiscount = $discountPerUnit * $quantity;

        return round($totalDiscount, 2);
    }

    /**
     * Apply stacking rules for multiple discounts.
     */
    public function applyStackingRules(array $discounts, bool $allowStacking = true): float
    {
        if (!$allowStacking && !empty($discounts)) {
            // Return highest discount only
            return max($discounts);
        }

        // Sum all discounts
        return array_sum($discounts);
    }
}
