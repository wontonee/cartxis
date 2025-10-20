<?php

namespace Vortex\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Vortex\Shop\Models\Order;
use Vortex\Product\Models\Product;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_sku',
        'product_name',
        'product_image',
        'quantity',
        'price',
        'total',
        'tax_amount',
        'discount_amount',
        'options',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'options' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order that owns the order item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product associated with the order item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the formatted price.
     *
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get the formatted total.
     *
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 2);
    }

    /**
     * Calculate the subtotal (quantity * price).
     *
     * @return float
     */
    public function calculateSubtotal(): float
    {
        return $this->quantity * $this->price;
    }

    /**
     * Calculate the total including tax and discount.
     *
     * @return float
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->calculateSubtotal();
        return $subtotal + $this->tax_amount - $this->discount_amount;
    }

    /**
     * Check if the product is still available.
     *
     * @return bool
     */
    public function isProductAvailable(): bool
    {
        if (!$this->product) {
            return false;
        }

        return $this->product->status === 1 && $this->product->quantity >= $this->quantity;
    }

    /**
     * Get the product URL.
     *
     * @return string|null
     */
    public function getProductUrlAttribute(): ?string
    {
        if (!$this->product) {
            return null;
        }

        return route('shop.products.show', ['slug' => $this->product->slug]);
    }

    /**
     * Get the product thumbnail URL.
     *
     * @return string|null
     */
    public function getProductThumbnailAttribute(): ?string
    {
        if ($this->product_image) {
            return $this->product_image;
        }

        if ($this->product && $this->product->mainImage) {
            return $this->product->mainImage->url;
        }

        return asset('images/placeholder.png');
    }

    /**
     * Scope a query to only include items for a specific order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $orderId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope a query to only include items for a specific product.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $productId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }
}
