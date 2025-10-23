<?php

namespace Vortex\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type', // 'flat-rate' or 'calculated'
        'status', // 'active' or 'inactive'
        'display_order',
        'base_cost',
        'cost_per_kg',
        'description',
        'is_default',
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
        'cost_per_kg' => 'decimal:4',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all shipping rates for this method
     */
    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    /**
     * Scope: Get active shipping methods
     */
    public static function active()
    {
        return static::where('status', 'active');
    }

    /**
     * Scope: Get by status
     */
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope: Ordered by display_order then name
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Get the default shipping method
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Set this as the default shipping method
     */
    public function setAsDefault()
    {
        ShippingMethod::where('is_default', true)->update(['is_default' => false]);
        $this->is_default = true;
        $this->save();
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
     * Calculate shipping cost for given weight and country/state
     */
    public function calculateCost($weight, $country = null, $state = null)
    {
        // For flat-rate method, return base cost
        if ($this->type === 'flat-rate') {
            return $this->base_cost;
        }

        // For calculated method, find matching rate
        $query = $this->rates()->where('status', 'active');

        if ($country) {
            $query->where('country', $country);
        }

        if ($state) {
            $query->where('state', $state);
        }

        $rate = $query->first();

        if (!$rate) {
            return null; // No matching rate
        }

        // Calculate: base + (weight * per_kg)
        return $rate->base_cost + ($weight * $rate->cost_per_kg);
    }

    /**
     * Get available rates for display
     */
    public function getAvailableRates()
    {
        return $this->rates()
            ->where('status', 'active')
            ->orderBy('country')
            ->orderBy('state')
            ->get();
    }
}
