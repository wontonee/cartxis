<?php

namespace Cartxis\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderDownload extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'product_download_id',
        'title',
        'file_path',
        'file_name',
        'token',
        'download_count',
        'max_downloads',
        'expires_at',
    ];

    protected $casts = [
        'download_count' => 'integer',
        'max_downloads' => 'integer',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (OrderDownload $download) {
            if (empty($download->token)) {
                $download->token = Str::random(48);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasReachedLimit(): bool
    {
        return $this->max_downloads !== null && $this->download_count >= $this->max_downloads;
    }

    public function canDownload(): bool
    {
        return ! $this->isExpired() && ! $this->hasReachedLimit() && Storage::disk('local')->exists($this->file_path);
    }

    public function recordDownload(): void
    {
        $this->increment('download_count');
    }
}
