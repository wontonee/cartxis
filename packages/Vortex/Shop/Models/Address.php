<?php

namespace Vortex\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'addresses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'addressable_type',
        'addressable_id',
        'type',
        'first_name',
        'last_name',
        'company',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Address type constants
     */
    const TYPE_BILLING = 'billing';
    const TYPE_SHIPPING = 'shipping';

    /**
     * Get all available address types.
     *
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_BILLING => 'Billing',
            self::TYPE_SHIPPING => 'Shipping',
        ];
    }

    /**
     * Get the parent addressable model (order or user).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the full address (single line).
     *
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the formatted address (multi-line).
     *
     * @return array
     */
    public function getFormattedAddressAttribute(): array
    {
        $lines = [];

        if ($this->full_name) {
            $lines[] = $this->full_name;
        }

        if ($this->company) {
            $lines[] = $this->company;
        }

        if ($this->address_line1) {
            $lines[] = $this->address_line1;
        }

        if ($this->address_line2) {
            $lines[] = $this->address_line2;
        }

        $cityStateZip = [];
        if ($this->city) {
            $cityStateZip[] = $this->city;
        }
        if ($this->state) {
            $cityStateZip[] = $this->state;
        }
        if ($this->postal_code) {
            $cityStateZip[] = $this->postal_code;
        }
        
        if (!empty($cityStateZip)) {
            $lines[] = implode(', ', $cityStateZip);
        }

        if ($this->country) {
            $lines[] = $this->country;
        }

        if ($this->phone) {
            $lines[] = 'Phone: ' . $this->phone;
        }

        return $lines;
    }

    /**
     * Check if this is a billing address.
     *
     * @return bool
     */
    public function isBilling(): bool
    {
        return $this->type === self::TYPE_BILLING;
    }

    /**
     * Check if this is a shipping address.
     *
     * @return bool
     */
    public function isShipping(): bool
    {
        return $this->type === self::TYPE_SHIPPING;
    }

    /**
     * Check if this is the default address.
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->is_default === true;
    }

    /**
     * Set this address as the default.
     *
     * @return void
     */
    public function setAsDefault(): void
    {
        // Remove default from other addresses of the same type
        self::where('addressable_type', $this->addressable_type)
            ->where('addressable_id', $this->addressable_id)
            ->where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Scope a query to only include billing addresses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBilling($query)
    {
        return $query->where('type', self::TYPE_BILLING);
    }

    /**
     * Scope a query to only include shipping addresses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeShipping($query)
    {
        return $query->where('type', self::TYPE_SHIPPING);
    }

    /**
     * Scope a query to only include default addresses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include addresses for a specific addressable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForAddressable($query, string $type, int $id)
    {
        return $query->where('addressable_type', $type)
                     ->where('addressable_id', $id);
    }

    /**
     * Validate the address is complete.
     *
     * @return bool
     */
    public function isComplete(): bool
    {
        $required = [
            'first_name',
            'last_name',
            'address_line1',
            'city',
            'state',
            'postal_code',
            'country',
        ];

        foreach ($required as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }

        return true;
    }
}
