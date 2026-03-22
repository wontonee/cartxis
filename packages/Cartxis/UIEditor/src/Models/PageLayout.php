<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Cartxis\CMS\Models\Page;

class PageLayout extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'uieditor_page_layouts';

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public const TYPE_CMS_PAGE = 'cms_page';
    public const TYPE_HOMEPAGE = 'homepage';

    protected $fillable = [
        'page_type',
        'page_id',
        'layout_data',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'layout_data'  => 'array',
        'published_at' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    // ─── Boot ─────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (PageLayout $layout) {
            if (auth('admin')->check()) {
                $layout->created_by = auth('admin')->id();
            }
        });

        static::updating(function (PageLayout $layout) {
            if (auth('admin')->check()) {
                $layout->updated_by = auth('admin')->id();
            }
        });
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForPage(Builder $query, Page $page): Builder
    {
        return $query->where('page_type', self::TYPE_CMS_PAGE)
                     ->where('page_id', $page->id);
    }

    public function scopeHomepage(Builder $query): Builder
    {
        return $query->where('page_type', self::TYPE_HOMEPAGE);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }
}
