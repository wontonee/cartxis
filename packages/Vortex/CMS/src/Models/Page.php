<?php

declare(strict_types=1);

namespace Vortex\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Vortex\Core\Models\Channel;
use App\Models\User;

class Page extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'pages';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'url_key',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'channel_id',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot method to auto-generate URL key and track user.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($page) {
            // Auto-generate URL key from title if not provided
            if (empty($page->url_key)) {
                $page->url_key = Str::slug($page->title);
            }

            // Auto-set meta_title from title if empty
            if (empty($page->meta_title)) {
                $page->meta_title = $page->title;
            }

            // Track creator
            if (auth('admin')->check()) {
                $page->created_by = auth('admin')->id();
            }
        });

        static::updating(function ($page) {
            // Track updater
            if (auth('admin')->check()) {
                $page->updated_by = auth('admin')->id();
            }
        });
    }

    /**
     * Get the channel that owns the page.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Get the admin user who created the page.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the admin user who last updated the page.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include draft pages.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include disabled pages.
     */
    public function scopeDisabled($query)
    {
        return $query->where('status', 'disabled');
    }

    /**
     * Get the full URL for the page.
     */
    public function getUrlAttribute(): string
    {
        return route('page.show', ['slug' => $this->url_key]);
    }

    /**
     * Get the status label with color.
     */
    public function getStatusLabelAttribute(): array
    {
        return match ($this->status) {
            'published' => ['text' => 'Published', 'class' => 'success'],
            'draft' => ['text' => 'Draft', 'class' => 'warning'],
            'disabled' => ['text' => 'Disabled', 'class' => 'danger'],
            default => ['text' => 'Unknown', 'class' => 'secondary'],
        };
    }
}
