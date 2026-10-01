<?php

namespace Cartxis\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class QuoteRequest extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'reference',
        'user_id',
        'product_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'company',
        'quantity',
        'message',
        'status',
        'admin_notes',
        'product_name',
        'product_sku',
        'product_price',
        'ip_address',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'product_price' => 'decimal:4',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (QuoteRequest $request) {
            if (empty($request->reference)) {
                $request->reference = 'QR-'.strtoupper(Str::random(8));
            }
            if (empty($request->status)) {
                $request->status = self::STATUS_NEW;
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
