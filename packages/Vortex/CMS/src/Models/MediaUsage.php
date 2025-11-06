<?php

namespace Vortex\CMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_file_id',
        'usable_type',
        'usable_id',
        'context',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method to update media file usage count
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $model->mediaFile->incrementUsageCount();
        });

        static::deleted(function ($model) {
            $model->mediaFile->decrementUsageCount();
        });
    }

    /**
     * Relationships
     */
    public function mediaFile()
    {
        return $this->belongsTo(MediaFile::class, 'media_file_id');
    }

    public function usable()
    {
        return $this->morphTo();
    }

    /**
     * Static methods
     */
    public static function track(int $mediaFileId, $usable, string $context = null): self
    {
        return static::firstOrCreate([
            'media_file_id' => $mediaFileId,
            'usable_type' => get_class($usable),
            'usable_id' => $usable->id,
            'context' => $context,
        ]);
    }

    public static function untrack(int $mediaFileId, $usable, string $context = null): void
    {
        static::where([
            'media_file_id' => $mediaFileId,
            'usable_type' => get_class($usable),
            'usable_id' => $usable->id,
            'context' => $context,
        ])->delete();
    }
}
