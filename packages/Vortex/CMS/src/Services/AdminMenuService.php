<?php

declare(strict_types=1);

namespace Vortex\CMS\Services;

use Vortex\CMS\Models\AdminMenuItem;
use Vortex\CMS\Repositories\AdminMenuRepository;

class AdminMenuService
{
    public function __construct(
        private AdminMenuRepository $repository
    ) {}

    /**
     * Get menu tree for admin sidebar
     */
    public function getMenuTree()
    {
        return $this->repository->getMenuTree();
    }

    /**
     * Get all menu items
     */
    public function getAllMenuItems()
    {
        return $this->repository->getAll();
    }

    /**
     * Create new menu item
     */
    public function createMenuItem(array $data): AdminMenuItem
    {
        // Set sort order if not provided
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = $this->repository->getMaxSortOrder($data['parent_id'] ?? null) + 1;
        }

        // Ensure either route or url is provided
        if (empty($data['route']) && empty($data['url'])) {
            throw new \InvalidArgumentException('Either route or url must be provided');
        }

        return $this->repository->create($data);
    }

    /**
     * Update menu item
     */
    public function updateMenuItem(AdminMenuItem $menuItem, array $data): AdminMenuItem
    {
        // Prevent setting parent_id to self
        if (isset($data['parent_id']) && $data['parent_id'] == $menuItem->id) {
            throw new \InvalidArgumentException('Cannot set menu item as its own parent');
        }

        return $this->repository->update($menuItem, $data);
    }

    /**
     * Delete menu item
     */
    public function deleteMenuItem(AdminMenuItem $menuItem): bool
    {
        return $this->repository->delete($menuItem);
    }

    /**
     * Reorder menu items
     */
    public function reorderMenuItems(array $items): void
    {
        $this->repository->updateSortOrder($items);
    }

    /**
     * Toggle menu item active status
     */
    public function toggleActive(AdminMenuItem $menuItem): AdminMenuItem
    {
        return $this->repository->toggleActive($menuItem);
    }

    /**
     * Get parent menu items for dropdown
     */
    public function getParentOptions()
    {
        return $this->repository->getParents();
    }
}
