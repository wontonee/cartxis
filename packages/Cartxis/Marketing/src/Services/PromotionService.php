<?php

namespace Cartxis\Marketing\Services;

use Cartxis\Marketing\Models\Promotion;
use Cartxis\Product\Models\Product;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * PromotionService
 * 
 * Handles promotion management and application.
 */
class PromotionService
{
    public function __construct(
        protected DiscountCalculator $calculator
    ) {}

    /**
     * Get all active catalog rule promotions.
     */
    public function getActiveCatalogRules(): Collection
    {
        return Promotion::active()
            ->catalogRules()
            ->byPriority()
            ->get();
    }

    /**
     * Get all active cart rule promotions.
     */
    public function getActiveCartRules(): Collection
    {
        return Promotion::active()
            ->cartRules()
            ->byPriority()
            ->get();
    }

    /**
     * Get active promotions with badges for product display.
     */
    public function getPromotionsWithBadges(): Collection
    {
        return Promotion::active()
            ->withBadge()
            ->byPriority()
            ->get();
    }

    /**
     * Find best promotion for a product.
     */
    public function findBestPromotionForProduct(Product $product, Collection $promotions = null): ?Promotion
    {
        if (!$promotions) {
            $promotions = $this->getActiveCatalogRules();
        }

        $applicablePromotions = [];

        foreach ($promotions as $promotion) {
            if ($this->isProductEligible($product, $promotion)) {
                $applicablePromotions[] = $promotion;
            }
        }

        if (empty($applicablePromotions)) {
            return null;
        }

        // Sort by priority (higher first), then by discount amount (higher first)
        usort($applicablePromotions, function($a, $b) {
            if ($a->priority !== $b->priority) {
                return $b->priority - $a->priority;
            }
            return $b->discount_value - $a->discount_value;
        });

        return $applicablePromotions[0];
    }

    /**
     * Check if product is eligible for promotion.
     */
    public function isProductEligible(Product $product, Promotion $promotion): bool
    {
        $actions = $promotion->actions ?? [];

        // Check if promotion has product restrictions
        $applicableProducts = $actions['applicable_products'] ?? null;
        $applicableCategories = $actions['applicable_categories'] ?? null;
        $excludedProducts = $actions['excluded_products'] ?? null;
        $excludedCategories = $actions['excluded_categories'] ?? null;

        // Check excluded products
        if ($excludedProducts && in_array($product->id, $excludedProducts)) {
            return false;
        }

        // Check excluded categories
        if ($excludedCategories && $product->categories) {
            $productCategoryIds = $product->categories->pluck('id')->toArray();
            if (array_intersect($excludedCategories, $productCategoryIds)) {
                return false;
            }
        }

        // If specific products are set, product must be in list
        if ($applicableProducts) {
            return in_array($product->id, $applicableProducts);
        }

        // If specific categories are set, product must be in one
        if ($applicableCategories && $product->categories) {
            $productCategoryIds = $product->categories->pluck('id')->toArray();
            return !empty(array_intersect($applicableCategories, $productCategoryIds));
        }

        // Check if product is explicitly linked via pivot table
        if ($promotion->products()->where('product_id', $product->id)->exists()) {
            return true;
        }

        // No restrictions = eligible
        return true;
    }

    /**
     * Apply catalog rule promotions to products.
     */
    public function applyPromotionsToProducts(Collection $products): Collection
    {
        $promotions = $this->getActiveCatalogRules();

        foreach ($products as $product) {
            $bestPromotion = $this->findBestPromotionForProduct($product, $promotions);

            if ($bestPromotion) {
                $product->original_price = $product->price;
                $product->promotional_price = $this->calculator->calculatePromotionalPrice(
                    $product->price,
                    $bestPromotion
                );
                $product->active_promotion = [
                    'id' => $bestPromotion->id,
                    'name' => $bestPromotion->name,
                    'badge_text' => $bestPromotion->badge_text,
                    'badge_color' => $bestPromotion->badge_color,
                    'badge_bg_color' => $bestPromotion->badge_bg_color,
                    'badge_position' => $bestPromotion->badge_position,
                    'show_countdown' => $bestPromotion->show_countdown,
                    'seconds_remaining' => $bestPromotion->seconds_remaining,
                ];
                $product->savings_amount = $product->price - $product->promotional_price;
                $product->savings_percentage = round(
                    (($product->price - $product->promotional_price) / $product->price) * 100
                );
            }
        }

        return $products;
    }

    /**
     * Check if cart meets promotion conditions.
     */
    public function meetsConditions(Collection $cartItems, float $cartSubtotal, Promotion $promotion, ?int $customerId = null): bool
    {
        $conditions = $promotion->conditions ?? [];

        // Check minimum order amount
        if (isset($conditions['min_order_amount'])) {
            if ($cartSubtotal < $conditions['min_order_amount']) {
                return false;
            }
        }

        // Check minimum items count
        if (isset($conditions['min_items'])) {
            if ($cartItems->count() < $conditions['min_items']) {
                return false;
            }
        }

        // Check minimum quantity (total items)
        if (isset($conditions['min_quantity'])) {
            $totalQuantity = $cartItems->sum('quantity');
            if ($totalQuantity < $conditions['min_quantity']) {
                return false;
            }
        }

        // Check customer groups
        if (isset($conditions['customer_groups']) && $customerId) {
            $customer = \Cartxis\Customer\Models\Customer::find($customerId);
            
            if (!$customer || !in_array($customer->group_id, $conditions['customer_groups'])) {
                return false;
            }
        }

        // Check if specific products are required in cart
        if (isset($conditions['requires_products'])) {
            $cartProductIds = $cartItems->pluck('product_id')->toArray();
            $hasRequired = !empty(array_intersect($conditions['requires_products'], $cartProductIds));
            
            if (!$hasRequired) {
                return false;
            }
        }

        // Check first order only
        if (isset($conditions['first_order_only']) && $conditions['first_order_only'] && $customerId) {
            $orderCount = \Cartxis\Shop\Models\Order::where('customer_id', $customerId)
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($orderCount > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Apply cart rule promotions.
     */
    public function applyCartRules(Collection $cartItems, float $cartSubtotal, ?int $customerId = null): array
    {
        $cartRules = $this->getActiveCartRules();
        $appliedPromotions = [];
        $totalDiscount = 0;

        foreach ($cartRules as $rule) {
            if (!$this->meetsConditions($cartItems, $cartSubtotal, $rule, $customerId)) {
                continue;
            }

            $discount = $this->calculator->calculatePromotionDiscount($rule, $cartSubtotal, $cartItems);

            $appliedPromotions[] = [
                'promotion_id' => $rule->id,
                'promotion_name' => $rule->name,
                'discount_amount' => $discount,
            ];

            $totalDiscount += $discount;

            // Stop processing if rule says so
            if ($rule->stop_rules_processing) {
                break;
            }
        }

        return [
            'total_discount' => $totalDiscount,
            'applied_promotions' => $appliedPromotions,
        ];
    }

    /**
     * Record promotion usage after order completion.
     */
    public function recordUsage(int $promotionId, int $orderId, float $orderTotal): void
    {
        $promotion = Promotion::find($promotionId);
        
        if ($promotion) {
            $promotion->incrementUsage();
            $promotion->incrementRevenue($orderTotal);
        }
    }

    /**
     * Get promotion analytics.
     */
    public function getAnalytics(int $promotionId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $promotion = Promotion::findOrFail($promotionId);

        // This is simplified - in production, you'd query order/promotion usage records
        return [
            'total_uses' => $promotion->usage_count,
            'total_revenue' => $promotion->total_revenue_generated,
            'conversion_count' => $promotion->conversion_count,
            'roi' => $promotion->roi,
            'average_order_value' => $promotion->conversion_count > 0 
                ? $promotion->total_revenue_generated / $promotion->conversion_count 
                : 0,
        ];
    }

    /**
     * Get tiered price for quantity.
     */
    public function getTieredPrice(Promotion $promotion, float $basePrice, int $quantity): float
    {
        if ($promotion->type !== Promotion::TYPE_TIERED_PRICING || !$promotion->price_tiers) {
            return $basePrice;
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
            return $basePrice;
        }

        $discountPercentage = $applicableTier['discount_percentage'] ?? 0;
        $discountAmount = ($basePrice * $discountPercentage) / 100;

        return round($basePrice - $discountAmount, 2);
    }

    /**
     * Bulk activate promotions.
     */
    public function bulkActivate(array $promotionIds): int
    {
        return Promotion::whereIn('id', $promotionIds)->update(['is_active' => true]);
    }

    /**
     * Bulk deactivate promotions.
     */
    public function bulkDeactivate(array $promotionIds): int
    {
        return Promotion::whereIn('id', $promotionIds)->update(['is_active' => false]);
    }

    /**
     * Bulk delete promotions (soft delete).
     */
    public function bulkDelete(array $promotionIds): int
    {
        return Promotion::whereIn('id', $promotionIds)->delete();
    }
}
