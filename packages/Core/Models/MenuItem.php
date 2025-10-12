<?php

namespace Vortex\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'menu_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'title',
        'icon',
        'route',
        'url',
        'parent_id',
        'order',
        'permission',
        'location',
        'active',
        'meta',
        'extension_code',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'active' => 'boolean',
        'meta' => 'array',
        'order' => 'integer',
    ];

    /**
     * Get the parent menu item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get the child menu items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->where('active', true)
            ->orderBy('order');
    }

    /**
     * Get all children recursively.
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Scope to get menu items by location.
     */
    public function scopeByLocation($query, string $location = 'admin')
    {
        return $query->where('location', $location);
    }

    /**
     * Scope to get only active menu items.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get only parent menu items.
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to order by the order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Check if menu item has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get the full URL for this menu item.
     */
    public function getUrlAttribute(): ?string
    {
        if ($this->attributes['url']) {
            return $this->attributes['url'];
        }

        if ($this->route) {
            return route($this->route);
        }

        return null;
    }
}
