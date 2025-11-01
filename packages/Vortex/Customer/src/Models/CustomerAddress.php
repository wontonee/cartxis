<?php

declare(strict_types=1);

namespace Vortex\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'type',
        'first_name',
        'last_name',
        'company',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'is_default_shipping',
        'is_default_billing',
    ];

    protected $casts = [
        'is_default_shipping' => 'boolean',
        'is_default_billing' => 'boolean',
    ];

    /**
     * Get the customer that owns this address.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the full name for this address.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get formatted address as a single string.
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = [
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ];

        return implode(', ', array_filter($parts));
    }

    /**
     * Scope query to shipping addresses.
     */
    public function scopeShipping($query)
    {
        return $query->where('type', 'shipping');
    }

    /**
     * Scope query to billing addresses.
     */
    public function scopeBilling($query)
    {
        return $query->where('type', 'billing');
    }

    /**
     * Set this address as default for its type.
     */
    public function setAsDefault(): void
    {
        $field = $this->type === 'shipping' ? 'is_default_shipping' : 'is_default_billing';

        // Remove default flag from other addresses
        static::where('customer_id', $this->customer_id)
            ->where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->update([$field => false]);

        // Set this as default
        $this->update([$field => true]);
    }
}
