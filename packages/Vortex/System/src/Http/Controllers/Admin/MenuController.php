<?php

namespace Vortex\System\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Vortex\Core\Models\MenuItem;

class MenuController
{
    /**
     * Display the menu management page.
     */
    public function index()
    {
        $menuItems = MenuItem::with(['parent', 'children'])
            ->byLocation('admin')
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/System/Menu/Index', [
            'menuItems' => $menuItems,
        ]);
    }

    /**
     * Store a newly created menu item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menu_items,id',
            'order' => 'nullable|integer',
            'permission' => 'nullable|string|max:255',
            'active' => 'boolean',
            'meta' => 'nullable|array',
            'extension_code' => 'nullable|string|max:255',
        ]);

        // Set location to admin
        $validated['location'] = 'admin';

        // Auto-generate key from title if not provided
        if (empty($validated['key'])) {
            $validated['key'] = \Str::slug($validated['title']);
        }

        // Auto-assign order if not provided
        if (!isset($validated['order'])) {
            $maxOrder = MenuItem::byLocation('admin')
                ->where('parent_id', $validated['parent_id'] ?? null)
                ->max('order');
            $validated['order'] = ($maxOrder ?? 0) + 1;
        }

        // Set default active if not provided
        if (!isset($validated['active'])) {
            $validated['active'] = true;
        }

        // Validate route or url is provided
        if (empty($validated['route']) && empty($validated['url'])) {
            return back()->withErrors(['route' => 'Either route or URL must be provided.']);
        }

        $menuItem = MenuItem::create($validated);

        return back()->with('success', 'Menu item created successfully.');
    }

    /**
     * Update the specified menu item.
     */
    public function update(Request $request, MenuItem $menu)
    {
        $validated = $request->validate([
            'key' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menu_items,id',
            'order' => 'nullable|integer',
            'permission' => 'nullable|string|max:255',
            'active' => 'boolean',
            'meta' => 'nullable|array',
            'extension_code' => 'nullable|string|max:255',
        ]);

        // Prevent circular parent relationship
        if (isset($validated['parent_id']) && $validated['parent_id'] == $menu->id) {
            return back()->withErrors(['parent_id' => 'A menu item cannot be its own parent.']);
        }

        // Check if setting parent would create a circular reference
        if (isset($validated['parent_id'])) {
            $parent = MenuItem::find($validated['parent_id']);
            if ($parent && $this->isDescendant($menu, $parent)) {
                return back()->withErrors(['parent_id' => 'Cannot set a descendant as parent.']);
            }
        }

        // Validate route or url is provided
        if (empty($validated['route']) && empty($validated['url'])) {
            return back()->withErrors(['route' => 'Either route or URL must be provided.']);
        }

        $menu->update($validated);

        return back()->with('success', 'Menu item updated successfully.');
    }

    /**
     * Remove the specified menu item.
     */
    public function destroy(MenuItem $menu)
    {
        // Delete children first
        $menu->children()->delete();
        
        $menu->delete();

        return back()->with('success', 'Menu item deleted successfully.');
    }

    /**
     * Reorder menu items via drag-drop.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.order' => 'required|integer',
            'items.*.parent_id' => 'nullable|exists:menu_items,id',
        ]);

        foreach ($validated['items'] as $item) {
            MenuItem::where('id', $item['id'])->update([
                'order' => $item['order'],
                'parent_id' => $item['parent_id'],
            ]);
        }

        return back()->with('success', 'Menu items reordered successfully.');
    }

    /**
     * Toggle menu item active status.
     */
    public function toggle(MenuItem $menu)
    {
        $menu->update(['active' => !$menu->active]);

        return back()->with('success', 'Menu item status updated successfully.');
    }

    /**
     * Check if a menu item is a descendant of another.
     */
    private function isDescendant(MenuItem $item, MenuItem $potentialDescendant): bool
    {
        if ($potentialDescendant->parent_id === $item->id) {
            return true;
        }

        if ($potentialDescendant->parent_id) {
            $parent = MenuItem::find($potentialDescendant->parent_id);
            if ($parent) {
                return $this->isDescendant($item, $parent);
            }
        }

        return false;
    }
}
