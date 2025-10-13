<?php

namespace Vortex\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'sort_order',
        'show_in_menu',
        'left',
        'right',
        'depth',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'show_in_menu' => 'boolean',
        'left' => 'integer',
        'right' => 'integer',
        'depth' => 'integer',
    ];

    /**
     * Get the status attribute as boolean for forms
     */
    public function getStatusAttribute($value): bool
    {
        return $value === 'enabled';
    }

    /**
     * Set the status attribute from boolean to enum
     */
    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = $value ? 'enabled' : 'disabled';
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all descendants (recursive)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get products in this category
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product')
            ->withTimestamps();
    }

    /**
     * Scope: Only enabled categories
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 'enabled');
    }

    /**
     * Scope: Root categories (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Show in menu
     */
    public function scopeShowInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }
}
