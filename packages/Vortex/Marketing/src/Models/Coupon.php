<?php

namespace Vortex\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'coupons';

    /**
     * Coupon type constants
     */
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED_AMOUNT = 'fixed_amount';
    const TYPE_FREE_SHIPPING = 'free_shipping';
    const TYPE_BUY_X_GET_Y = 'buy_x_get_y';
    const TYPE_FIXED_PRICE = 'fixed_price';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'max_discount',
        'min_order_amount',
        'is_active',
        'is_public',
        'auto_apply',
        'stackable',
        'exclude_sale_items',
        'priority',
        'usage_limit_total',
        'usage_limit_per_customer',
        'usage_count',
        'start_date',
        'end_date',
        'days_of_week',
        'time_restrictions',
        'customer_groups',
        'first_order_only',
        'min_account_age_days',
        'applicable_products',
        'applicable_categories',
        'excluded_products',
        'excluded_categories',
        'buy_quantity',
        'get_quantity',
        'buy_products',
        'get_products',
        'created_by',
        'internal_notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'auto_apply' => 'boolean',
        'stackable' => 'boolean',
        'exclude_sale_items' => 'boolean',
        'first_order_only' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'days_of_week' => 'json',
        'time_restrictions' => 'json',
        'customer_groups' => 'json',
        'applicable_products' => 'json',
        'applicable_categories' => 'json',
        'excluded_products' => 'json',
        'excluded_categories' => 'json',
        'buy_products' => 'json',
        'get_products' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who created the coupon.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all usage records for this coupon.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Scope: Get active coupons.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope: Get public coupons.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope: Get auto-apply coupons.
     */
    public function scopeAutoApply(Builder $query): Builder
    {
        return $query->where('auto_apply', true);
    }

    /**
     * Scope: Search by code (case-insensitive).
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->whereRaw('LOWER(code) = ?', [strtolower($code)]);
    }

    /**
     * Check if coupon is currently valid (basic check).
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        // Check usage limit
        if ($this->usage_limit_total && $this->usage_count >= $this->usage_limit_total) {
            return false;
        }

        return true;
    }

    /**
     * Check if coupon is valid for specific day/time.
     */
    public function isValidForDateTime(Carbon $dateTime = null): bool
    {
        $dateTime = $dateTime ?? now();

        // Check day of week
        if ($this->days_of_week) {
            $dayName = strtolower($dateTime->format('l'));
            if (!in_array($dayName, $this->days_of_week)) {
                return false;
            }
        }

        // Check time restrictions
        if ($this->time_restrictions) {
            $currentTime = $dateTime->format('H:i');
            $start = $this->time_restrictions['start'] ?? null;
            $end = $this->time_restrictions['end'] ?? null;

            if ($start && $currentTime < $start) {
                return false;
            }

            if ($end && $currentTime > $end) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get remaining usage count.
     */
    public function getRemainingUsageAttribute(): ?int
    {
        if (!$this->usage_limit_total) {
            return null; // Unlimited
        }

        return max(0, $this->usage_limit_total - $this->usage_count);
    }

    /**
     * Check if coupon has usage limit.
     */
    public function hasUsageLimit(): bool
    {
        return $this->usage_limit_total !== null;
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Get usage count for specific customer.
     */
    public function getCustomerUsageCount(int $customerId): int
    {
        return $this->usages()
            ->where('customer_id', $customerId)
            ->count();
    }

    /**
     * Check if customer can use this coupon.
     */
    public function canBeUsedByCustomer(int $customerId): bool
    {
        if (!$this->usage_limit_per_customer) {
            return true; // No per-customer limit
        }

        $usageCount = $this->getCustomerUsageCount($customerId);
        return $usageCount < $this->usage_limit_per_customer;
    }
}
