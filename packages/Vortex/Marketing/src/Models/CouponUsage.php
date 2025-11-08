<?php

namespace Vortex\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Vortex\Customer\Models\Customer;
use Vortex\Shop\Models\Order;

class CouponUsage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'coupon_usage';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'coupon_id',
        'order_id',
        'customer_id',
        'user_id',
        'discount_amount',
        'order_subtotal',
        'used_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'discount_amount' => 'decimal:2',
        'order_subtotal' => 'decimal:2',
        'used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the coupon that was used.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the order this coupon was used on.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the customer who used this coupon.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who used this coupon.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the discount percentage (discount/subtotal * 100).
     */
    public function getDiscountPercentageAttribute(): float
    {
        if ($this->order_subtotal == 0) {
            return 0;
        }

        return round(($this->discount_amount / $this->order_subtotal) * 100, 2);
    }
}
