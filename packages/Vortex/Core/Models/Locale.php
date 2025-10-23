<?php

namespace Vortex\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locale extends Model
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
        'native_name',
        'direction',
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

        // When setting a locale as default, unset other defaults
        static::saving(function ($locale) {
            if ($locale->is_default) {
                static::where('id', '!=', $locale->id)
                    ->update(['is_default' => false]);
            }
        });

        // Ensure at least one locale is default
        static::deleted(function ($locale) {
            if ($locale->is_default) {
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
     * Scope a query to only include active locales.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include the default locale.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the default locale.
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Get locale by code.
     */
    public static function getByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
