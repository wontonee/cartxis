<?php

declare(strict_types=1);

namespace Cartxis\CMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Block extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'identifier',
        'title',
        'type',
        'content',
        'status',
        'start_date',
        'end_date',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($block) {
            if (auth('admin')->check()) {
                $block->created_by = auth('admin')->id();
            }
        });

        static::updating(function ($block) {
            if (auth('admin')->check()) {
                $block->updated_by = auth('admin')->id();
            }
        });
    }

    /**
     * Get the admin user who created the block.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the admin user who last updated the block.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include active blocks.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive blocks.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope a query to only include scheduled blocks (within start/end dates).
     */
    public function scopeScheduled($query)
    {
        $now = now();
        
        return $query->where(function ($q) use ($now) {
            $q->where(function ($subQ) use ($now) {
                // Has start date and it's started
                $subQ->whereNotNull('start_date')
                    ->where('start_date', '<=', $now);
            })
            ->orWhereNull('start_date');
        })
        ->where(function ($q) use ($now) {
            $q->where(function ($subQ) use ($now) {
                // Has end date and it's not ended
                $subQ->whereNotNull('end_date')
                    ->where('end_date', '>=', $now);
            })
            ->orWhereNull('end_date');
        });
    }

    /**
     * Check if block is currently visible based on schedule.
     */
    public function isVisible(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        // Check start date
        if ($this->start_date && $this->start_date > $now) {
            return false;
        }

        // Check end date
        if ($this->end_date && $this->end_date < $now) {
            return false;
        }

        return true;
    }

    /**
     * Get the content decoded as array (for JSON types).
     */
    public function getContentArrayAttribute(): ?array
    {
        if (empty($this->content)) {
            return null;
        }

        $decoded = json_decode($this->content, true);
        
        return is_array($decoded) ? $decoded : null;
    }
}
