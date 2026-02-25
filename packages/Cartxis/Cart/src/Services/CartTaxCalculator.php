<?php

namespace Cartxis\Cart\Services;

use Cartxis\Product\Models\Product;
use Cartxis\Core\Models\TaxClass;
use Cartxis\Core\Models\TaxRate;
use Cartxis\Core\Models\TaxRule;

/**
 * Cart Tax Calculator Service
 * 
 * Calculates taxes for cart items based on tax classes and tax rules.
 * Provides hooks for theme customization.
 */
class CartTaxCalculator
{
    /**
     * Calculate taxes for all cart items
     * 
     * @param array $cartItems Cart items array
     * @param array $customerAddress Customer address (for tax zone calculation)
     * @return array Tax breakdown
     */
    public function calculate(array $cartItems, array $customerAddress = []): array
    {
        // Fire hook before calculation
        if (function_exists('Hook') && class_exists(\Cartxis\Core\Facades\Hook::class)) {
            \Cartxis\Core\Facades\Hook::doAction('cart.tax.calculating', $cartItems, $customerAddress);
        }

        $taxBreakdown = [];
        $taxTotal = 0;

        foreach ($cartItems as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];

            // Get product with tax class
            $product = Product::with('taxClass')->find($productId);

            if (!$product || !$product->taxClass) {
                // No tax class assigned, skip
                continue;
            }

            $taxClass = $product->taxClass;

            // Get applicable tax rates for this tax class
            $taxRates = $this->getApplicableTaxRates($taxClass->id, $customerAddress);

            foreach ($taxRates as $taxRate) {
                $lineTotal = $price * $quantity;
                $taxAmount = $lineTotal * ($taxRate->percentage / 100);

                $taxKey = $taxClass->name . ' (' . number_format($taxRate->percentage, 2) . '%)';

                if (isset($taxBreakdown[$taxKey])) {
                    $taxBreakdown[$taxKey]['amount'] += $taxAmount;
                } else {
                    $taxBreakdown[$taxKey] = [
                        'tax_class' => $taxClass->name,
                        'tax_class_id' => $taxClass->id,
                        'rate' => $taxRate->percentage,
                        'rate_id' => $taxRate->id,
                        'amount' => $taxAmount,
                        'label' => $taxKey,
                    ];
                }

                $taxTotal += $taxAmount;
            }
        }

        $result = [
            'breakdown' => array_values($taxBreakdown),
            'total' => $taxTotal,
        ];

        // Apply filter hook for customization
        if (function_exists('Hook') && class_exists(\Cartxis\Core\Facades\Hook::class)) {
            $result = \Cartxis\Core\Facades\Hook::applyFilter('cart.taxes', $result, $cartItems, $customerAddress);
        }

        // Fire hook after calculation
        if (function_exists('Hook') && class_exists(\Cartxis\Core\Facades\Hook::class)) {
            \Cartxis\Core\Facades\Hook::doAction('cart.tax.calculated', $result, $cartItems);
        }

        return $result;
    }

    /**
     * Get applicable tax rates for a tax class
     * 
     * @param int $taxClassId Tax class ID
     * @param array $customerAddress Customer address
     * @return \Illuminate\Support\Collection
     */
    private function getApplicableTaxRates(int $taxClassId, array $customerAddress)
    {
        // Get tax rules for this tax class
        $taxRules = TaxRule::where('tax_class_id', $taxClassId)
            ->with('taxRate')
            ->get();

        if ($taxRules->isEmpty()) {
            return collect();
        }

        // For now, return all active tax rates for the tax class
        // In future, we can filter by customer address (country, state, zip)
        $taxRates = $taxRules->map(function ($rule) {
            return $rule->taxRate;
        })->filter();

        // Apply priority (higher priority wins if multiple rates for same zone)
        $taxRates = $taxRates->sortByDesc('priority');

        return $taxRates;
    }

    /**
     * Calculate tax for a single item
     * 
     * @param array $item Cart item
     * @param array $customerAddress Customer address
     * @return float Tax amount
     */
    public function calculateForItem(array $item, array $customerAddress = []): float
    {
        $result = $this->calculate([$item], $customerAddress);
        return $result['total'];
    }

    /**
     * Get tax breakdown as formatted string
     * 
     * @param array $cartItems Cart items
     * @param array $customerAddress Customer address
     * @return string Formatted tax breakdown
     */
    public function getFormattedBreakdown(array $cartItems, array $customerAddress = []): string
    {
        $result = $this->calculate($cartItems, $customerAddress);

        if (empty($result['breakdown'])) {
            return 'No taxes applicable';
        }

        $lines = [];
        foreach ($result['breakdown'] as $tax) {
            $lines[] = $tax['label'] . ': $' . number_format($tax['amount'], 2);
        }

        return implode("\n", $lines);
    }
}
