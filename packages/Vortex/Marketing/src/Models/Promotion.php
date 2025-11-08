<?php

namespace Vortex\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Vortex\Product\Models\Product;
use Carbon\Carbon;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'promotions';

    /**
     * Promotion type constants
     */
    const TYPE_CATALOG_RULE = 'catalog_rule';
    const TYPE_CART_RULE = 'cart_rule';
    const TYPE_BUNDLE = 'bundle';
    const TYPE_FLASH_SALE = 'flash_sale';
    const TYPE_TIERED_PRICING = 'tiered_pricing';

    /**
     * Discount type constants
     */
    const DISCOUNT_PERCENTAGE = 'percentage';
    const DISCOUNT_FIXED_AMOUNT = 'fixed_amount';

    /**
     * Badge position constants
     */
    const BADGE_TOP_LEFT = 'top-left';
    const BADGE_TOP_RIGHT = 'top-right';
    const BADGE_BOTTOM_LEFT = 'bottom-left';
    const BADGE_BOTTOM_RIGHT = 'bottom-right';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'discount_type',
        'discount_value',
        'max_discount',
        'is_active',
        'stop_rules_processing',
        'priority',
        'stackable',
        'stackable_with_coupons',
        'show_badge',
        'badge_text',
        'badge_color',
        'badge_bg_color',
        'badge_position',
        'show_countdown',
        'start_date',
        'end_date',
        'usage_limit',
        'usage_count',
        'usage_per_customer',
        'conditions',
        'actions',
        'bundle_products',
        'price_tiers',
        'total_revenue_generated',
        'conversion_count',
        'created_by',
        'internal_notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'is_active' => 'boolean',
        'stop_rules_processing' => 'boolean',
        'stackable' => 'boolean',
        'stackable_with_coupons' => 'boolean',
        'show_badge' => 'boolean',
        'show_countdown' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'conditions' => 'json',
        'actions' => 'json',
        'bundle_products' => 'json',
        'price_tiers' => 'json',
        'total_revenue_generated' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who created the promotion.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all products explicitly assigned to this promotion.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_products')
            ->withPivot(['discount_override', 'badge_text_override'])
            ->withTimestamps();
    }

    /**
     * Scope: Get active promotions.
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
     * Scope: Get promotions by type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Get catalog rule promotions.
     */
    public function scopeCatalogRules(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_CATALOG_RULE);
    }

    /**
     * Scope: Get cart rule promotions.
     */
    public function scopeCartRules(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_CART_RULE);
    }

    /**
     * Scope: Get promotions with badges.
     */
    public function scopeWithBadge(Builder $query): Builder
    {
        return $query->where('show_badge', true);
    }

    /**
     * Scope: Order by priority (highest first).
     */
    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Check if promotion is currently valid.
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
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if promotion is a flash sale.
     */
    public function isFlashSale(): bool
    {
        return $this->type === self::TYPE_FLASH_SALE;
    }

    /**
     * Get time remaining for flash sale.
     */
    public function getTimeRemaining(): ?Carbon
    {
        if (!$this->isFlashSale() || !$this->end_date) {
            return null;
        }

        return $this->end_date->diffForHumans();
    }

    /**
     * Get seconds remaining for countdown.
     */
    public function getSecondsRemainingAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        $diff = now()->diffInSeconds($this->end_date, false);
        return max(0, $diff);
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Increment revenue generated.
     */
    public function incrementRevenue(float $amount): void
    {
        $this->increment('total_revenue_generated', $amount);
        $this->increment('conversion_count');
    }

    /**
     * Get ROI percentage.
     */
    public function getRoiAttribute(): float
    {
        if ($this->total_revenue_generated == 0) {
            return 0;
        }

        // Simple ROI calculation (this can be enhanced)
        $discountGiven = $this->usage_count * $this->discount_value;
        if ($discountGiven == 0) {
            return 0;
        }

        return round((($this->total_revenue_generated - $discountGiven) / $discountGiven) * 100, 2);
    }
}
