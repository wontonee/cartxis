<?php

namespace Cartxis\Marketing\Services;

use Cartxis\Marketing\Models\Coupon;
use Cartxis\Marketing\Models\CouponUsage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * CouponService
 * 
 * Handles coupon validation, application, and management.
 */
class CouponService
{
    public function __construct(
        protected DiscountCalculator $calculator
    ) {}

    /**
     * Find coupon by code (case-insensitive).
     */
    public function findByCode(string $code): ?Coupon
    {
        return Coupon::byCode($code)->first();
    }

    /**
     * Validate coupon for application.
     * 
     * @return array ['valid' => bool, 'message' => string, 'coupon' => ?Coupon]
     */
    public function validate(string $code, ?int $customerId = null, float $cartTotal = 0, Collection $cartItems = null): array
    {
        // 1. Find coupon (case-insensitive)
        $coupon = $this->findByCode($code);

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'Coupon code not found.',
                'coupon' => null,
            ];
        }

        // 2. Check if active
        if (!$coupon->is_active) {
            return [
                'valid' => false,
                'message' => 'This coupon is no longer active.',
                'coupon' => $coupon,
            ];
        }

        // 3. Check date range
        $now = now();

        if ($coupon->start_date && $now->lt($coupon->start_date)) {
            return [
                'valid' => false,
                'message' => 'This coupon is not yet active. Valid from: ' . $coupon->start_date->format('M d, Y'),
                'coupon' => $coupon,
            ];
        }

        if ($coupon->end_date && $now->gt($coupon->end_date)) {
            return [
                'valid' => false,
                'message' => 'This coupon has expired.',
                'coupon' => $coupon,
            ];
        }

        // 4. Check total usage limit
        if ($coupon->usage_limit_total && $coupon->usage_count >= $coupon->usage_limit_total) {
            return [
                'valid' => false,
                'message' => 'This coupon has reached its usage limit.',
                'coupon' => $coupon,
            ];
        }

        // 5. Check per-customer usage limit
        if ($customerId && $coupon->usage_limit_per_customer) {
            $customerUsageCount = $coupon->getCustomerUsageCount($customerId);

            if ($customerUsageCount >= $coupon->usage_limit_per_customer) {
                return [
                    'valid' => false,
                    'message' => 'You have already used this coupon the maximum number of times.',
                    'coupon' => $coupon,
                ];
            }
        }

        // 6. Check day of week restriction
        if (!$coupon->isValidForDateTime()) {
            $allowedDays = implode(', ', array_map('ucfirst', $coupon->days_of_week ?? []));
            return [
                'valid' => false,
                'message' => "This coupon is only valid on: {$allowedDays}",
                'coupon' => $coupon,
            ];
        }

        // 7. Check minimum order amount
        if ($coupon->min_order_amount && $cartTotal < $coupon->min_order_amount) {
                $currency = \Cartxis\Core\Models\Currency::getDefault();
            $minAmount = $currency->format($coupon->min_order_amount);
            return [
                'valid' => false,
                'message' => "Minimum order amount of {$minAmount} required.",
                'coupon' => $coupon,
            ];
        }

        // 8. Check first order only restriction
        if ($coupon->first_order_only && $customerId) {
                $orderCount = \Cartxis\Shop\Models\Order::where('customer_id', $customerId)
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($orderCount > 0) {
                return [
                    'valid' => false,
                    'message' => 'This coupon is only valid for first-time customers.',
                    'coupon' => $coupon,
                ];
            }
        }

        // 9. Check customer group eligibility
        if ($coupon->customer_groups && $customerId) {
                $customer = \Cartxis\Customer\Models\Customer::find($customerId);
            
            if (!$customer || !in_array($customer->group_id, $coupon->customer_groups)) {
                return [
                    'valid' => false,
                    'message' => 'This coupon is not available for your customer group.',
                    'coupon' => $coupon,
                ];
            }
        }

        // 10. Check account age requirement
        if ($coupon->min_account_age_days && $customerId) {
                $customer = \Cartxis\Customer\Models\Customer::find($customerId);
            
            if ($customer) {
                $accountAge = now()->diffInDays($customer->created_at);
                
                if ($accountAge < $coupon->min_account_age_days) {
                    return [
                        'valid' => false,
                        'message' => "This coupon requires an account age of at least {$coupon->min_account_age_days} days.",
                        'coupon' => $coupon,
                    ];
                }
            }
        }

        // All validations passed
        return [
            'valid' => true,
            'message' => 'Coupon is valid.',
            'coupon' => $coupon,
        ];
    }

    /**
     * Apply coupon to cart and calculate discount.
     */
    public function apply(Coupon $coupon, float $cartTotal, Collection $cartItems): array
    {
        $discountAmount = $this->calculator->calculateCouponDiscount($coupon, $cartTotal, $cartItems);

        return [
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'discount_type' => $coupon->type,
            'discount_amount' => $discountAmount,
            'message' => $this->getDiscountMessage($coupon, $discountAmount),
        ];
    }

    /**
     * Record coupon usage after order completion.
     */
    public function recordUsage(
        int $couponId, 
        int $orderId, 
        float $discountAmount, 
        float $orderSubtotal,
        ?int $customerId = null,
        ?int $userId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): CouponUsage {
        $usage = CouponUsage::create([
            'coupon_id' => $couponId,
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'user_id' => $userId,
            'discount_amount' => $discountAmount,
            'order_subtotal' => $orderSubtotal,
            'used_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        // Increment coupon usage count
        $coupon = Coupon::find($couponId);
        $coupon->incrementUsage();

        return $usage;
    }

    /**
     * Get list of active public coupons.
     */
    public function getPublicCoupons(): Collection
    {
        return Coupon::active()
            ->public()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get auto-apply coupons for cart.
     */
    public function getAutoApplyCoupons(?int $customerId = null): Collection
    {
        $query = Coupon::active()->autoApply();

        // Filter by customer group if applicable
        if ($customerId) {
            $customer = \Cartxis\Customer\Models\Customer::find($customerId);
            
            if ($customer && $customer->group_id) {
                $query->where(function ($q) use ($customer) {
                    $q->whereNull('customer_groups')
                        ->orWhereJsonContains('customer_groups', $customer->group_id);
                });
            }
        }

        return $query->orderBy('priority', 'desc')->get();
    }

    /**
     * Get discount message for display.
     */
    protected function getDiscountMessage(Coupon $coupon, float $discountAmount): string
    {
        $currency = \Cartxis\Core\Models\Currency::getDefault();
        $formatted = $currency->format($discountAmount);

        return match($coupon->type) {
            Coupon::TYPE_PERCENTAGE => "Coupon applied: {$coupon->value}% off ({$formatted} discount)",
            Coupon::TYPE_FIXED_AMOUNT => "Coupon applied: {$formatted} off",
            Coupon::TYPE_FREE_SHIPPING => "Free shipping applied",
            Coupon::TYPE_BUY_X_GET_Y => "Buy {$coupon->buy_quantity} get {$coupon->get_quantity} free applied ({$formatted} discount)",
            Coupon::TYPE_FIXED_PRICE => "Special pricing applied ({$formatted} discount)",
            default => "Coupon applied",
        };
    }

    /**
     * Get coupon analytics.
     */
    public function getAnalytics(int $couponId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $coupon = Coupon::findOrFail($couponId);

        $query = CouponUsage::where('coupon_id', $couponId);

        if ($startDate) {
            $query->where('used_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('used_at', '<=', $endDate);
        }

        $usages = $query->get();

        return [
            'total_uses' => $usages->count(),
            'unique_customers' => $usages->pluck('customer_id')->unique()->count(),
            'total_discount_given' => $usages->sum('discount_amount'),
            'average_discount' => $usages->avg('discount_amount'),
            'average_order_value' => $usages->avg('order_subtotal'),
            'usage_by_day' => $this->getUsageByDay($usages),
            'top_customers' => $this->getTopCustomers($couponId),
        ];
    }

    /**
     * Get usage grouped by day.
     */
    protected function getUsageByDay(Collection $usages): array
    {
        return $usages->groupBy(function ($usage) {
            return $usage->used_at->format('Y-m-d');
        })->map(function ($dayUsages) {
            return [
                'count' => $dayUsages->count(),
                'total_discount' => $dayUsages->sum('discount_amount'),
            ];
        })->toArray();
    }

    /**
     * Get top customers by usage.
     */
    protected function getTopCustomers(int $couponId, int $limit = 10): Collection
    {
        return CouponUsage::where('coupon_id', $couponId)
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('COUNT(*) as usage_count'), DB::raw('SUM(discount_amount) as total_discount'))
            ->groupBy('customer_id')
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->with('customer')
            ->get();
    }

    /**
     * Bulk activate coupons.
     */
    public function bulkActivate(array $couponIds): int
    {
        return Coupon::whereIn('id', $couponIds)->update(['is_active' => true]);
    }

    /**
     * Bulk deactivate coupons.
     */
    public function bulkDeactivate(array $couponIds): int
    {
        return Coupon::whereIn('id', $couponIds)->update(['is_active' => false]);
    }

    /**
     * Bulk delete coupons (soft delete).
     */
    public function bulkDelete(array $couponIds): int
    {
        return Coupon::whereIn('id', $couponIds)->delete();
    }
}
