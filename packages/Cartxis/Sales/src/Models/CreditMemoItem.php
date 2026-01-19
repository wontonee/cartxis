<?php

namespace Cartxis\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cartxis\Shop\Models\OrderItem;
use Cartxis\Product\Models\Product;

class CreditMemoItem extends Model
{
    protected $fillable = [
        'credit_memo_id',
        'order_item_id',
        'product_id',
        'product_name',
        'sku',
        'qty_refunded',
        'price',
        'tax_amount',
        'discount_amount',
        'row_total',
        'restore_stock',
        'stock_restored',
    ];

    protected $casts = [
        'qty_refunded' => 'integer',
        'price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'row_total' => 'decimal:2',
        'restore_stock' => 'boolean',
        'stock_restored' => 'boolean',
    ];

    /**
     * Get the credit memo for this item
     */
    public function creditMemo(): BelongsTo
    {
        return $this->belongsTo(CreditMemo::class);
    }

    /**
     * Get the order item for this credit memo item
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the product for this credit memo item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
