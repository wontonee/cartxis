<?php

namespace Cartxis\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'symbol_position',
        'decimal_places',
        'exchange_rate',
        'is_default',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'decimal_places' => 'integer',
        'exchange_rate' => 'decimal:10',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When setting a currency as default, unset other defaults
        static::saving(function ($currency) {
            if ($currency->is_default) {
                static::where('id', '!=', $currency->id)
                    ->update(['is_default' => false]);
            }
        });

        // Ensure at least one currency is default
        static::deleted(function ($currency) {
            if ($currency->is_default) {
                $newDefault = static::where('is_active', true)
                    ->orderBy('sort_order')
                    ->first();
                
                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }
        });
    }

    /**
     * Scope a query to only include active currencies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include the default currency.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the default currency.
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Get currency by code.
     */
    public static function getByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    /**
     * Format an amount with this currency.
     */
    public function format(float $amount): string
    {
        $formattedAmount = number_format(
            $amount,
            $this->decimal_places,
            '.',
            ','
        );

        return $this->symbol_position === 'before'
            ? $this->symbol . $formattedAmount
            : $formattedAmount . $this->symbol;
    }

    /**
     * Convert amount from base currency to this currency.
     */
    public function convertFrom(float $amount): float
    {
        return $amount * $this->exchange_rate;
    }

    /**
     * Convert amount from this currency to base currency.
     */
    public function convertTo(float $amount): float
    {
        return $this->exchange_rate > 0
            ? $amount / $this->exchange_rate
            : 0;
    }
}
