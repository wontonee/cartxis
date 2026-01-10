<?php

namespace Vortex\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'path',
        'thumbnail_path',
        'alt_text',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * Attributes to append
     */
    protected $appends = ['url'];

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the full URL for the image
     */
    public function getUrlAttribute(): string
    {
        // Check if path is already a full URL (e.g., Unsplash, external CDN)
        if (filter_var($this->path, FILTER_VALIDATE_URL)) {
            return $this->path;
        }
        
        // For local files, prepend storage path
        return asset('storage/' . $this->path);
    }
}
