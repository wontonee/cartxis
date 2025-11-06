<?php

namespace Vortex\CMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'filename',
        'original_filename',
        'path',
        'disk',
        'mime_type',
        'size',
        'extension',
        'folder_id',
        'alt_text',
        'title',
        'description',
        'width',
        'height',
        'thumbnails',
        'used_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'thumbnails' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'used_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot method to auto-track creator and updater
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        // Delete physical files when model is deleted
        static::deleting(function ($model) {
            if ($model->isForceDeleting()) {
                $model->deletePhysicalFiles();
            }
        });
    }

    /**
     * Relationships
     */
    public function folder()
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function usages()
    {
        return $this->hasMany(MediaUsage::class, 'media_file_id');
    }

    /**
     * Scopes
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeVideos($query)
    {
        return $query->where('mime_type', 'like', 'video/%');
    }

    public function scopeDocuments($query)
    {
        return $query->whereIn('mime_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    public function scopeInFolder($query, ?int $folderId)
    {
        return $folderId ? $query->where('folder_id', $folderId) : $query->whereNull('folder_id');
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('original_filename', 'like', "%{$search}%")
                ->orWhere('alt_text', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Accessors
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getIsVideoAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function getIsDocumentAttribute(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->is_image || !$this->thumbnails) {
            return null;
        }

        // Return smallest thumbnail for preview
        $sizes = ['150', '600', '1200'];
        foreach ($sizes as $size) {
            if (isset($this->thumbnails[$size])) {
                return Storage::disk($this->disk)->url($this->thumbnails[$size]);
            }
        }

        return $this->url;
    }

    /**
     * Methods
     */
    public function getThumbnail(int $size): ?string
    {
        if (!$this->is_image || !$this->thumbnails) {
            return null;
        }

        if (isset($this->thumbnails[$size])) {
            return Storage::disk($this->disk)->url($this->thumbnails[$size]);
        }

        return $this->url;
    }

    public function incrementUsageCount(): void
    {
        $this->increment('used_count');
    }

    public function decrementUsageCount(): void
    {
        if ($this->used_count > 0) {
            $this->decrement('used_count');
        }
    }

    public function deletePhysicalFiles(): void
    {
        // Delete main file
        if (Storage::disk($this->disk)->exists($this->path)) {
            Storage::disk($this->disk)->delete($this->path);
        }

        // Delete thumbnails
        if ($this->thumbnails) {
            foreach ($this->thumbnails as $thumbnailPath) {
                if (Storage::disk($this->disk)->exists($thumbnailPath)) {
                    Storage::disk($this->disk)->delete($thumbnailPath);
                }
            }
        }
    }
}
