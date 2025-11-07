<?php

declare(strict_types=1);

namespace Vortex\CMS\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Vortex\CMS\Models\Block;

class BlockRepository
{
    /**
     * Find a block by its identifier.
     */
    public function findByIdentifier(string $identifier): ?Block
    {
        return Block::where('identifier', $identifier)
            ->active()
            ->scheduled()
            ->first();
    }

    /**
     * Get all active and scheduled blocks.
     */
    public function getActive(array $filters = []): Collection
    {
        $query = Block::active()->scheduled();

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['channel_id'])) {
            $query->where('channel_id', $filters['channel_id']);
        }

        return $query->orderBy('title')->get();
    }

    /**
     * Get paginated blocks with filters and search.
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Block::query();

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('identifier', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by type
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by channel
        if (!empty($filters['channel_id'])) {
            $query->where('channel_id', $filters['channel_id']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->with(['creator', 'updater'])->paginate($perPage);
    }

    /**
     * Get count by status.
     */
    public function getCountByStatus(): array
    {
        return [
            'total' => Block::count(),
            'active' => Block::where('status', 'active')->count(),
            'inactive' => Block::where('status', 'inactive')->count(),
            'scheduled' => Block::active()
                ->where(function ($q) {
                    $q->whereNotNull('start_date')
                        ->orWhereNotNull('end_date');
                })
                ->count(),
        ];
    }

    /**
     * Get blocks by type.
     */
    public function getByType(string $type): Collection
    {
        return Block::where('type', $type)
            ->active()
            ->scheduled()
            ->orderBy('title')
            ->get();
    }

    /**
     * Search blocks.
     */
    public function search(string $query): Collection
    {
        return Block::where('title', 'like', "%{$query}%")
            ->orWhere('identifier', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->orderBy('title')
            ->get();
    }
}
