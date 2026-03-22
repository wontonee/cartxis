<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class GlobalRegion extends Model
{
    use SoftDeletes;

    protected $table = 'uieditor_global_regions';

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public const TYPE_HEADER  = 'header';
    public const TYPE_FOOTER  = 'footer';
    public const TYPE_SECTION = 'section';
    public const TYPE_BANNER  = 'banner';
    public const TYPE_SIDEBAR = 'sidebar';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'region_type',
        'layout_data',
        'published_layout_data',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'layout_data'           => 'array',
        'published_layout_data' => 'array',
        'published_at'          => 'datetime',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
        'deleted_at'            => 'datetime',
    ];

    // ─── Boot ─────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (GlobalRegion $region) {
            if (empty($region->slug)) {
                $region->slug = Str::slug($region->name);
            }
            if (auth('admin')->check()) {
                $region->created_by = auth('admin')->id();
            }
        });

        static::updating(function (GlobalRegion $region) {
            if (auth('admin')->check()) {
                $region->updated_by = auth('admin')->id();
            }
        });
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('region_type', $type);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Returns the storefront-safe layout (published snapshot).
     * Falls back to draft so editors can preview unpublished regions.
     */
    public function getStorefrontLayout(): ?array
    {
        return $this->published_layout_data ?? $this->layout_data;
    }
}
