<?php

declare(strict_types=1);

namespace Vortex\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'password',
        'customer_group_id',
        'company_name',
        'tax_id',
        'is_active',
        'is_verified',
        'is_guest',
        'newsletter_subscribed',
        'total_orders',
        'total_spent',
        'average_order_value',
        'last_order_date',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'is_guest' => 'boolean',
        'newsletter_subscribed' => 'boolean',
        'total_orders' => 'integer',
        'total_spent' => 'decimal:2',
        'average_order_value' => 'decimal:2',
        'last_order_date' => 'datetime',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the customer group this customer belongs to.
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    /**
     * Get all addresses for this customer.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    /**
     * Get the default shipping address.
     */
    public function defaultShippingAddress(): HasMany
    {
        return $this->addresses()->where('is_default_shipping', true);
    }

    /**
     * Get the default billing address.
     */
    public function defaultBillingAddress(): HasMany
    {
        return $this->addresses()->where('is_default_billing', true);
    }

    /**
     * Get all notes for this customer.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(CustomerNote::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the customer's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope query to only active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to only verified customers.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Update customer statistics.
     */
    public function updateStatistics(): void
    {
        // This would be called after an order is placed
        // Statistics would be calculated from the orders table
        // For now, this is a placeholder
    }
}
