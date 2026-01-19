<?php

declare(strict_types=1);

namespace Cartxis\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cartxis\Product\Models\Product;

class Wishlist extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'wishlists';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'customer_id',
        'product_id',
    ];

    /**
     * Get the customer that owns the wishlist item.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the product associated with the wishlist item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
