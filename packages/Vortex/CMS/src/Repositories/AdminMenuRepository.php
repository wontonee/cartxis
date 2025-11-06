<?php

declare(strict_types=1);

namespace Vortex\CMS\Repositories;

use Illuminate\Support\Collection;
use Vortex\CMS\Models\AdminMenuItem;

class AdminMenuRepository
{
    /**
     * Get all menu items with hierarchy
     */
    public function getMenuTree(): Collection
    {
        return AdminMenuItem::with('children')
            ->parents()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get all menu items (flat list)
     */
    public function getAll(): Collection
    {
        return AdminMenuItem::with('parent', 'children')
            ->ordered()
            ->get();
    }

    /**
     * Get parent menu items only
     */
    public function getParents(): Collection
    {
        return AdminMenuItem::parents()
            ->ordered()
            ->get();
    }

    /**
     * Find menu item by ID
     */
    public function find(int $id): ?AdminMenuItem
    {
        return AdminMenuItem::with('parent', 'children')->find($id);
    }

    /**
     * Create a new menu item
     */
    public function create(array $data): AdminMenuItem
    {
        return AdminMenuItem::create($data);
    }

    /**
     * Update menu item
     */
    public function update(AdminMenuItem $menuItem, array $data): AdminMenuItem
    {
        $menuItem->update($data);
        return $menuItem->fresh();
    }

    /**
     * Delete menu item
     */
    public function delete(AdminMenuItem $menuItem): bool
    {
        // Delete children first
        $menuItem->children()->delete();
        
        return $menuItem->delete();
    }

    /**
     * Update sort order for multiple items
     */
    public function updateSortOrder(array $items): void
    {
        foreach ($items as $item) {
            AdminMenuItem::where('id', $item['id'])
                ->update([
                    'sort_order' => $item['sort_order'],
                    'parent_id' => $item['parent_id'] ?? null,
                ]);
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive(AdminMenuItem $menuItem): AdminMenuItem
    {
        $menuItem->update(['is_active' => !$menuItem->is_active]);
        return $menuItem->fresh();
    }

    /**
     * Get max sort order
     */
    public function getMaxSortOrder(?int $parentId = null): int
    {
        return AdminMenuItem::where('parent_id', $parentId)
            ->max('sort_order') ?? 0;
    }
}
