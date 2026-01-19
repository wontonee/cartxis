<?php

namespace Cartxis\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_required',
        'is_filterable',
        'is_configurable',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'is_configurable' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attribute) {
            if (empty($attribute->code)) {
                $attribute->code = Str::slug($attribute->name, '_');
            }
            
            // Auto-set sort_order if not provided
            if (empty($attribute->sort_order)) {
                $attribute->sort_order = (static::max('sort_order') ?? 0) + 1;
            }
        });
    }

    /**
     * Get attribute options
     */
    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class)->orderBy('sort_order');
    }

    /**
     * Get product attribute values
     */
    public function productValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    /**
     * Get products that have this attribute
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values')
            ->withPivot('attribute_option_id', 'text_value', 'boolean_value', 'date_value')
            ->withTimestamps();
    }

    /**
     * Scope: Only filterable attributes
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope: Only configurable attributes (used for variants)
     */
    public function scopeConfigurable($query)
    {
        return $query->where('is_configurable', true);
    }

    /**
     * Scope: Search by name or code
     */
    public function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    /**
     * Scope: Filter by type
     */
    public function scopeOfType($query, $type)
    {
        if (!empty($type)) {
            return $query->where('type', $type);
        }
        return $query;
    }

    /**
     * Check if attribute is used by any products
     */
    public function isUsedByProducts(): bool
    {
        return $this->productValues()->exists();
    }

    /**
     * Get count of products using this attribute
     */
    public function getProductsCountAttribute(): int
    {
        return $this->productValues()->distinct('product_id')->count('product_id');
    }
}
