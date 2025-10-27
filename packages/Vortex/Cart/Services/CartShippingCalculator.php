<?php

namespace Vortex\Cart\Services;

use Vortex\Core\Models\ShippingMethod;

/**
 * Cart Shipping Calculator Service
 * 
 * Calculates shipping costs for cart items based on shipping methods.
 * Provides hooks for theme customization.
 */
class CartShippingCalculator
{
    /**
     * Calculate shipping options for cart items
     * 
     * @param array $cartItems Cart items array
     * @param array $shippingAddress Shipping address
     * @return array Available shipping methods with calculated costs
     */
    public function calculate(array $cartItems, array $shippingAddress = []): array
    {
        // Fire hook before calculation
        if (function_exists('Hook') && class_exists(\Vortex\Core\Facades\Hook::class)) {
            \Vortex\Core\Facades\Hook::doAction('cart.shipping.calculating', $cartItems, $shippingAddress);
        }

        // Calculate cart totals for shipping calculation
        $cartSubtotal = collect($cartItems)->sum(fn($item) => $item['price'] * $item['quantity']);
        $cartWeight = $this->calculateTotalWeight($cartItems);

        // Get all active shipping methods
        $shippingMethods = ShippingMethod::where('status', 'active')
            ->orderBy('display_order')
            ->get();

        $shippingOptions = [];

        foreach ($shippingMethods as $method) {
            // Calculate cost based on method type
            $cost = $this->calculateShippingCost($method, $cartSubtotal, $cartWeight);

            $shippingOptions[] = [
                'id' => $method->id,
                'code' => $method->code,
                'name' => $method->name,
                'description' => $method->description,
                'cost' => $cost,
                'estimated_days' => $method->estimated_days,
                'sort_order' => $method->sort_order,
            ];
        }

        $result = [
            'options' => $shippingOptions,
            'default' => $shippingOptions[0] ?? null,
        ];

        // Apply filter hook for customization
        if (function_exists('Hook') && class_exists(\Vortex\Core\Facades\Hook::class)) {
            $result = \Vortex\Core\Facades\Hook::applyFilter('cart.shipping', $result, $cartItems, $shippingAddress);
        }

        // Fire hook after calculation
        if (function_exists('Hook') && class_exists(\Vortex\Core\Facades\Hook::class)) {
            \Vortex\Core\Facades\Hook::doAction('cart.shipping.calculated', $result, $cartItems);
        }

        return $result;
    }

    /**
     * Calculate shipping cost for a specific method
     * 
     * @param ShippingMethod $method Shipping method
     * @param float $cartSubtotal Cart subtotal
     * @param float $cartWeight Total cart weight
     * @return float Calculated shipping cost
     */
    private function calculateShippingCost(ShippingMethod $method, float $cartSubtotal, float $cartWeight): float
    {
        // Free shipping if cart meets minimum
        if ($method->free_shipping_min > 0 && $cartSubtotal >= $method->free_shipping_min) {
            return 0;
        }

        // Base cost
        $cost = $method->base_cost;

        // Add per-item cost
        // Note: per_item_cost is not in the shipping_methods table yet
        // You may need to add this column or use a different calculation

        // Add weight-based cost (if weight_based_cost column exists)
        // $cost += $cartWeight * $method->weight_based_cost;

        return $cost;
    }

    /**
     * Calculate total weight of cart items
     * 
     * @param array $cartItems Cart items
     * @return float Total weight in kg
     */
    private function calculateTotalWeight(array $cartItems): float
    {
        // TODO: Implement weight calculation when products have weight attribute
        // For now, return 0
        return 0;

        // Future implementation:
        // $totalWeight = 0;
        // foreach ($cartItems as $item) {
        //     $product = Product::find($item['product_id']);
        //     if ($product && $product->weight) {
        //         $totalWeight += $product->weight * $item['quantity'];
        //     }
        // }
        // return $totalWeight;
    }

    /**
     * Get cheapest shipping option
     * 
     * @param array $cartItems Cart items
     * @param array $shippingAddress Shipping address
     * @return array|null Cheapest shipping option
     */
    public function getCheapestOption(array $cartItems, array $shippingAddress = []): ?array
    {
        $result = $this->calculate($cartItems, $shippingAddress);

        if (empty($result['options'])) {
            return null;
        }

        $cheapest = collect($result['options'])->sortBy('cost')->first();

        return $cheapest;
    }

    /**
     * Get shipping option by method ID
     * 
     * @param int $methodId Shipping method ID
     * @param array $cartItems Cart items
     * @param array $shippingAddress Shipping address
     * @return array|null Shipping option
     */
    public function getOptionById(int $methodId, array $cartItems, array $shippingAddress = []): ?array
    {
        $result = $this->calculate($cartItems, $shippingAddress);

        return collect($result['options'])->firstWhere('id', $methodId);
    }
}
