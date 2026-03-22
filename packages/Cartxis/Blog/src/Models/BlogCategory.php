<?php

declare(strict_types=1);

namespace Cartxis\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'meta_title',
        'meta_description',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function activePosts(): HasMany
    {
        return $this->hasMany(BlogPost::class)->where('status', 'published');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
