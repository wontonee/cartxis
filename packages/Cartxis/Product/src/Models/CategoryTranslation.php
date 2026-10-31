<?php

declare(strict_types=1);

namespace Cartxis\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryTranslation extends Model
{
    protected $fillable = [
        'category_id',
        'locale',
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
