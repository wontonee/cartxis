<?php

declare(strict_types=1);

namespace Cartxis\CMS\Repositories;

use Cartxis\CMS\Models\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageRepository
{
    /**
     * Find a page by its URL key.
     */
    public function findByUrlKey(string $urlKey): ?Page
    {
        return Page::where('url_key', $urlKey)->first();
    }

    /**
     * Get published pages with optional filters.
     */
    public function getPublished(array $filters = []): Collection
    {
        $query = Page::published();

        return $query->orderBy('title')->get();
    }

    /**
     * Search pages by title or content.
     */
    public function search(string $searchTerm): Collection
    {
        return Page::whereRaw('MATCH(title, content) AGAINST(? IN BOOLEAN MODE)', [$searchTerm])
            ->orWhere('title', 'like', "%{$searchTerm}%")
            ->orWhere('url_key', 'like', "%{$searchTerm}%")
            ->orderByRaw('MATCH(title, content) AGAINST(? IN BOOLEAN MODE) DESC', [$searchTerm])
            ->get();
    }

    /**
     * Get paginated pages with filters.
     */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Page::query()->with(['creator', 'updater']);

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('url_key', 'like', "%{$search}%")
                  ->orWhereRaw('MATCH(title, content) AGAINST(? IN BOOLEAN MODE)', [$search]);
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get count by status.
     */
    public function getCountByStatus(): array
    {
        return [
            'total' => Page::count(),
            'published' => Page::where('status', 'published')->count(),
            'draft' => Page::where('status', 'draft')->count(),
            'disabled' => Page::where('status', 'disabled')->count(),
        ];
    }
}
