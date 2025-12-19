<?php

namespace Vortex\Core\Services;

use Vortex\Core\Models\MenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MenuService
{
    /**
     * Build menu tree for a specific location.
     */
    public function buildTree(string $location = 'admin'): Collection
    {
        $items = MenuItem::byLocation($location)
            ->active()
            ->parents()
            ->ordered()
            ->with('children')
            ->get();

        return $this->filterByPermission($items);
    }

    /**
     * Filter menu items by user permissions.
     */
    protected function filterByPermission(Collection $items): Collection
    {
        $user = Auth::user();

        return $items->filter(function ($item) use ($user) {
            if (!$this->canAccess($item, $user)) {
                return false;
            }

            if ($item->children) {
                $item->setRelation('children', $this->filterByPermission($item->children));
            }

            return true;
        })->values();
    }

    /**
     * Check if user can access menu item.
     */
    protected function canAccess(MenuItem $item, $user): bool
    {
        if (!$item->permission) {
            return true;
        }

        if (!$user) {
            return false;
        }

        return $user->hasPermission($item->permission);
    }

    /**
     * Register a new menu item.
     */
    public function register(array $data): MenuItem
    {
        return MenuItem::create($data);
    }

    /**
     * Unregister a menu item by key.
     */
    public function unregister(string $key): bool
    {
        return MenuItem::where('key', $key)->delete() > 0;
    }

    /**
     * Update a menu item.
     */
    public function update(string $key, array $data): bool
    {
        return MenuItem::where('key', $key)->update($data) > 0;
    }

    /**
     * Get menu item by key.
     */
    public function getByKey(string $key): ?MenuItem
    {
        return MenuItem::where('key', $key)->first();
    }

    /**
     * Get menu items by extension.
     */
    public function getByExtension(string $extensionCode): Collection
    {
        return MenuItem::where('extension_code', $extensionCode)->get();
    }

    /**
     * Remove menu items by extension.
     */
    public function removeByExtension(string $extensionCode): bool
    {
        return MenuItem::where('extension_code', $extensionCode)->delete() > 0;
    }

    /**
     * Reorder menu items.
     */
    public function reorder(array $items): void
    {
        foreach ($items as $order => $itemId) {
            MenuItem::where('id', $itemId)->update(['order' => $order]);
        }
    }

    /**
     * Get flat menu structure for admin management.
     */
    public function getFlatList(string $location = 'admin'): Collection
    {
        return MenuItem::byLocation($location)
            ->ordered()
            ->with('parent')
            ->get();
    }
}
