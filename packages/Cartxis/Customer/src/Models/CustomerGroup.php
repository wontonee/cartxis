<?php

declare(strict_types=1);

namespace Cartxis\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'color',
        'discount_percentage',
        'order',
        'is_default',
        'auto_assignment_rules',
        'status',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'order' => 'integer',
        'is_default' => 'boolean',
        'status' => 'boolean',
        'auto_assignment_rules' => 'array',
    ];

    /**
     * Get all customers in this group.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get active customers in this group.
     */
    public function activeCustomers(): HasMany
    {
        return $this->customers()->where('is_active', true);
    }

    /**
     * Scope query to only active groups.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get the default customer group.
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
