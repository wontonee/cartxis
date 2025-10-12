<?php

namespace Vortex\Core\Repositories;

use Vortex\Core\Models\MenuItem;
use Illuminate\Support\Collection;

class MenuRepository
{
    /**
     * Find menu item by ID.
     */
    public function find(int $id): ?MenuItem
    {
        return MenuItem::find($id);
    }

    /**
     * Find menu item by key.
     */
    public function findByKey(string $key): ?MenuItem
    {
        return MenuItem::where('key', $key)->first();
    }

    /**
     * Get all menu items for a location.
     */
    public function getByLocation(string $location = 'admin'): Collection
    {
        return MenuItem::byLocation($location)->ordered()->get();
    }

    /**
     * Get parent menu items for a location.
     */
    public function getParents(string $location = 'admin'): Collection
    {
        return MenuItem::byLocation($location)->parents()->ordered()->get();
    }

    /**
     * Get children of a menu item.
     */
    public function getChildren(int $parentId): Collection
    {
        return MenuItem::where('parent_id', $parentId)->active()->ordered()->get();
    }

    /**
     * Create a new menu item.
     */
    public function create(array $data): MenuItem
    {
        return MenuItem::create($data);
    }

    /**
     * Update a menu item.
     */
    public function update(int $id, array $data): bool
    {
        return MenuItem::where('id', $id)->update($data) > 0;
    }

    /**
     * Delete a menu item.
     */
    public function delete(int $id): bool
    {
        return MenuItem::where('id', $id)->delete() > 0;
    }

    /**
     * Get menu items by extension.
     */
    public function getByExtension(string $extensionCode): Collection
    {
        return MenuItem::where('extension_code', $extensionCode)->get();
    }

    /**
     * Delete menu items by extension.
     */
    public function deleteByExtension(string $extensionCode): bool
    {
        return MenuItem::where('extension_code', $extensionCode)->delete() > 0;
    }

    /**
     * Reorder menu items.
     */
    public function reorder(array $items): void
    {
        foreach ($items as $order => $itemId) {
            $this->update($itemId, ['order' => $order]);
        }
    }

    /**
     * Toggle menu item active status.
     */
    public function toggleActive(int $id): bool
    {
        $item = $this->find($id);
        
        if (!$item) {
            return false;
        }

        return $this->update($id, ['active' => !$item->active]);
    }
}
