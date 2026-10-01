<?php

namespace Cartxis\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductDownload extends Model
{
    protected $fillable = [
        'product_id',
        'title',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'max_downloads',
        'sort_order',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'max_downloads' => 'integer',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function absolutePath(): string
    {
        return Storage::disk('local')->path($this->file_path);
    }

    public function existsOnDisk(): bool
    {
        return Storage::disk('local')->exists($this->file_path);
    }
}
