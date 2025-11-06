<?php

namespace Vortex\CMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaFolder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'parent_id',
        'path',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot method to auto-track creator and build path
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }

            // Auto-generate path
            $model->path = $model->buildPath();
        });

        static::updating(function ($model) {
            // Update path if parent changed
            if ($model->isDirty('parent_id') || $model->isDirty('name')) {
                $model->path = $model->buildPath();
            }
        });

        // Update children paths when parent path changes
        static::updated(function ($model) {
            if ($model->wasChanged('path')) {
                $model->updateChildrenPaths();
            }
        });
    }

    /**
     * Relationships
     */
    public function parent()
    {
        return $this->belongsTo(MediaFolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MediaFolder::class, 'parent_id')->orderBy('sort_order');
    }

    public function files()
    {
        return $this->hasMany(MediaFile::class, 'folder_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeRootFolders($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    /**
     * Methods
     */
    public function buildPath(): string
    {
        if (!$this->parent_id) {
            return '/' . $this->name;
        }

        $parent = $this->parent;
        return $parent->path . '/' . $this->name;
    }

    public function updateChildrenPaths(): void
    {
        foreach ($this->children as $child) {
            $child->path = $child->buildPath();
            $child->saveQuietly(); // Save without triggering events again
        }
    }

    public function getFilesCountAttribute(): int
    {
        return $this->files()->count();
    }

    public function getTotalFilesCountAttribute(): int
    {
        $count = $this->files()->count();
        
        foreach ($this->children as $child) {
            $count += $child->total_files_count;
        }
        
        return $count;
    }

    public function getAllDescendants(): \Illuminate\Support\Collection
    {
        $descendants = collect([$this]);
        
        foreach ($this->children as $child) {
            $descendants = $descendants->merge($child->getAllDescendants());
        }
        
        return $descendants;
    }
}
