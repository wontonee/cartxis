<?php

namespace Vortex\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $table = 'shipping_rates';

    protected $fillable = [
        'shipping_method_id',
        'country',
        'state', // nullable - null means all states for country
        'min_weight',
        'max_weight',
        'base_cost',
        'cost_per_kg',
        'status',
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
        'cost_per_kg' => 'decimal:4',
        'min_weight' => 'decimal:3',
        'max_weight' => 'decimal:3',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the shipping method this rate belongs to
     */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    /**
     * Scope: Get active rates
     */
    public static function active()
    {
        return static::where('status', 'active');
    }

    /**
     * Scope: Get by country and state
     */
    public function scopeByLocation($query, $country, $state = null)
    {
        $query->where('country', $country);

        if ($state) {
            $query->where(function ($q) use ($state) {
                $q->whereNull('state')
                  ->orWhere('state', $state);
            });
        } else {
            $query->whereNull('state');
        }

        return $query;
    }

    /**
     * Scope: Get by weight range
     */
    public function scopeByWeight($query, $weight)
    {
        return $query->where('min_weight', '<=', $weight)
                     ->where('max_weight', '>=', $weight);
    }

    /**
     * Scope: Ordered by country, state, weight range
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('country')
                     ->orderBy('state')
                     ->orderBy('min_weight');
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus()
    {
        $this->status = $this->status === 'active' ? 'inactive' : 'active';
        $this->save();
        return $this;
    }

    /**
     * Get human-readable location string
     */
    public function getLocationLabel()
    {
        if ($this->state) {
            return "{$this->country} - {$this->state}";
        }
        return $this->country;
    }

    /**
     * Get human-readable weight range
     */
    public function getWeightRangeLabel()
    {
        return "{$this->min_weight} - {$this->max_weight} kg";
    }
}
