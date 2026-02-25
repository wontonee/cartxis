<?php

namespace Cartxis\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxRate extends Model
{
    protected $fillable = [
        'code',
        'name',
        'percentage',
        'priority',
        'is_compound',
        'is_active',
    ];

    protected $casts = [
        'percentage' => 'decimal:4',
        'priority' => 'integer',
        'is_compound' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function calculate(float $amount, float $previousTaxes = 0): float
    {
        $base = $this->is_compound ? ($amount + $previousTaxes) : $amount;
        return $base * ($this->percentage / 100);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('priority');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(TaxRule::class);
    }
}
