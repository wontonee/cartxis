<?php

namespace Vortex\CMS\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Vortex\CMS\Models\MediaFile;
use Vortex\CMS\Models\MediaFolder;

class MediaRepository
{
    /**
     * Get paginated media files with filters
     */
    public function paginate(array $filters = [], int $perPage = 24): LengthAwarePaginator
    {
        $query = MediaFile::with(['folder', 'creator']);

        // Filter by folder
        if (isset($filters['folder_id'])) {
            $query->inFolder($filters['folder_id']);
        }

        // Filter by type
        if (isset($filters['type'])) {
            match ($filters['type']) {
                'images' => $query->images(),
                'videos' => $query->videos(),
                'documents' => $query->documents(),
                default => null,
            };
        }

        // Search
        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get all files in a folder
     */
    public function getByFolder(?int $folderId): Collection
    {
        return MediaFile::inFolder($folderId)
            ->with(['folder', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Search media files
     */
    public function search(string $query, int $limit = 50): Collection
    {
        return MediaFile::search($query)
            ->with(['folder', 'creator'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get recently uploaded files
     */
    public function getRecent(int $limit = 20): Collection
    {
        return MediaFile::with(['folder', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unused files (used_count = 0)
     */
    public function getUnused(int $limit = 50): Collection
    {
        return MediaFile::where('used_count', 0)
            ->with(['folder', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get media statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_files' => MediaFile::count(),
            'total_images' => MediaFile::images()->count(),
            'total_videos' => MediaFile::videos()->count(),
            'total_documents' => MediaFile::documents()->count(),
            'total_size' => MediaFile::sum('size'),
            'unused_files' => MediaFile::where('used_count', 0)->count(),
        ];
    }

    /**
     * Get all folders
     */
    public function getAllFolders(): Collection
    {
        return MediaFolder::with(['parent', 'children'])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get root folders
     */
    public function getRootFolders(): Collection
    {
        return MediaFolder::rootFolders()
            ->with(['children'])
            ->get();
    }

    /**
     * Find folder by ID
     */
    public function findFolder(int $id): ?MediaFolder
    {
        return MediaFolder::with(['parent', 'children', 'files'])->find($id);
    }

    /**
     * Get files by IDs
     */
    public function findByIds(array $ids): Collection
    {
        return MediaFile::whereIn('id', $ids)
            ->with(['folder', 'creator'])
            ->get();
    }
}
