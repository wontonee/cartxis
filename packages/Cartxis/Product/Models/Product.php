<?php

namespace Cartxis\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Product type constants
     */
    const TYPE_SIMPLE = 'simple';
    const TYPE_CONFIGURABLE = 'configurable';
    const TYPE_VIRTUAL = 'virtual';
    const TYPE_DOWNLOADABLE = 'downloadable';

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'tax_class_id',
        'cost',
        'special_price',
        'special_price_from',
        'special_price_to',
        'status',
        'visibility',
        'featured',
        'new',
        'quantity',
        'stock_status',
        'manage_stock',
        'min_quantity',
        'max_quantity',
        'notify_stock_qty',
        'type',
        'weight',
        'length',
        'width',
        'height',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'sort_order',
        'views_count',
        'sales_count',
        'main_image_id',
        'brand_id',
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'cost' => 'decimal:4',
        'special_price' => 'decimal:4',
        'special_price_from' => 'date',
        'special_price_to' => 'date',
        'featured' => 'boolean',
        'new' => 'boolean',
        'manage_stock' => 'boolean',
        'quantity' => 'integer',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'notify_stock_qty' => 'integer',
        'sort_order' => 'integer',
        'views_count' => 'integer',
        'sales_count' => 'integer',
    ];

    protected $appends = [
        'in_stock',
        'image',
        'rating',
        'reviews_count',
        'has_configurable_attributes',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Get the images for the product
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    /**
     * Get the main image
     */
    public function mainImage()
    {
        return $this->belongsTo(ProductImage::class, 'main_image_id');
    }

    /**
     * Get the categories
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product')
            ->withTimestamps();
    }

    /**
     * Get the brand
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the tax class
     */
    public function taxClass()
    {
        return $this->belongsTo(\Cartxis\Core\Models\TaxClass::class);
    }

    /**
     * Get attribute values
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
    
    /**
     * Get attribute options (for product variations like size, color)
     */
    public function attributeOptions(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeOption::class, 
            'product_attribute_values', 
            'product_id', 
            'attribute_option_id'
        )->withTimestamps();
    }

    /**
     * Get variants (for configurable products)
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_variants', 'product_id', 'variant_id')
            ->withTimestamps();
    }

    /**
     * Get parent products (if this is a variant)
     */
    public function parentProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_variants', 'variant_id', 'product_id')
            ->withTimestamps();
    }

    /**
     * Get order items (for popularity sorting)
     */
    public function orderItems()
    {
        return $this->hasMany(\Cartxis\Shop\Models\OrderItem::class);
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        if (!$this->manage_stock) {
            return true;
        }

        return $this->quantity > 0 && $this->stock_status === 'in_stock';
    }

    /**
     * Get in_stock attribute
     */
    public function getInStockAttribute(): bool
    {
        return $this->isInStock();
    }

    /**
     * Get image attribute (main image URL)
     */
    public function getImageAttribute(): ?string
    {
        if ($this->mainImage) {
            return $this->mainImage->url ?? $this->mainImage->path ?? null;
        }
        
        $firstImage = $this->images()->first();
        return $firstImage ? ($firstImage->url ?? $firstImage->path ?? null) : null;
    }

    /**
     * Get rating attribute
     */
    public function getRatingAttribute(): float
    {
        return round($this->approvedReviews()->avg('rating') ?? 0, 1);
    }

    /**
     * Get reviews_count attribute
     */
    public function getReviewsCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Check if product has configurable attributes (color, size, etc.)
     */
    public function getHasConfigurableAttributesAttribute(): bool
    {
        return $this->attributeValues()
            ->whereHas('attribute', function ($query) {
                $query->where('is_configurable', true);
            })
            ->exists();
    }

    /**
     * Check if product has special price active
     */
    public function hasSpecialPrice(): bool
    {
        if (!$this->special_price) {
            return false;
        }

        $now = now();

        if ($this->special_price_from && $now->lt($this->special_price_from)) {
            return false;
        }

        if ($this->special_price_to && $now->gt($this->special_price_to)) {
            return false;
        }

        return true;
    }

    /**
     * Get final price (considering special price)
     */
    public function getFinalPrice()
    {
        if ($this->hasSpecialPrice()) {
            return $this->special_price;
        }

        return $this->price;
    }

    /**
     * Scope: Only enabled products
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 'enabled');
    }

    /**
     * Scope: Featured products
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope: New products
     */
    public function scopeNew($query)
    {
        return $query->where('new', true);
    }

    /**
     * Scope: In stock
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock')
            ->where('quantity', '>', 0);
    }

    /**
     * Get the reviews for the product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Get approved reviews for the product.
     */
    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('status', 'approved');
    }

    /**
     * Get the average rating for the product.
     */
    public function getAverageRatingAttribute(): float
    {
        return round($this->approvedReviews()->avg('rating') ?? 0, 1);
    }

    /**
     * Check if product is physical (requires shipping)
     */
    public function isPhysical(): bool
    {
        return in_array($this->type, [self::TYPE_SIMPLE, self::TYPE_CONFIGURABLE]);
    }

    /**
     * Check if product is virtual (no shipping)
     */
    public function isVirtual(): bool
    {
        return $this->type === self::TYPE_VIRTUAL;
    }

    /**
     * Check if product is downloadable (digital file)
     */
    public function isDownloadable(): bool
    {
        return $this->type === self::TYPE_DOWNLOADABLE;
    }

    /**
     * Check if product requires shipping
     */
    public function requiresShipping(): bool
    {
        return $this->isPhysical();
    }

    /**
     * Check if product is configurable (has variants)
     */
    public function isConfigurable(): bool
    {
        return $this->type === self::TYPE_CONFIGURABLE;
    }

    /**
     * Get the review count for the product.
     */
    public function getReviewCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }
}
