<?php

namespace Cartxis\Core\Models;

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
     * The accessors to append to the model's array form.
     */
    protected $appends = ['full_url'];

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
    public function getFullUrlAttribute(): ?string
    {
        // If there's an explicit URL, use it
        if (!empty($this->attributes['url'])) {
            return $this->attributes['url'];
        }

        // If there's a route name, generate the URL
        if (!empty($this->attributes['route'])) {
            try {
                // Use route() to generate URL, but extract only the path
                $fullUrl = route($this->attributes['route']);
                
                // Parse URL and return only the path component
                $parsed = parse_url($fullUrl);
                return $parsed['path'] ?? '#';
            } catch (\Exception $e) {
                return '#';
            }
        }

        return '#';
    }
}
