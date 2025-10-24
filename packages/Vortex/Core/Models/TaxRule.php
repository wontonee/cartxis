<?php

namespace Vortex\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRule extends Model
{
    protected $fillable = [
        'name',
        'tax_class_id',
        'tax_zone_id',
        'tax_rate_id',
        'priority',
        'calculate_shipping',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'calculate_shipping' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }

    public function taxZone(): BelongsTo
    {
        return $this->belongsTo(TaxZone::class);
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('priority');
    }
}
