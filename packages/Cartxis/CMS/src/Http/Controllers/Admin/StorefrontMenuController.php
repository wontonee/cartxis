<?php

declare(strict_types=1);

namespace Cartxis\CMS\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Cartxis\Core\Models\MenuItem;

class StorefrontMenuController extends Controller
{
    /**
     * Display storefront menu management page.
     */
    public function index(): Response
    {
        // Load all header menu items (both parents and children)
        $headerMenuItems = MenuItem::where('location', 'storefront')
            ->where('menu_type', 'header')
            ->with('parent')
            ->orderBy('order')
            ->get();

        // Load all footer menu items (both parents and children)
        $footerMenuItems = MenuItem::where('location', 'storefront')
            ->where('menu_type', 'footer')
            ->with('parent')
            ->orderBy('order')
            ->get();

        // Load all mobile menu items (both parents and children)
        $mobileMenuItems = MenuItem::where('location', 'storefront')
            ->where('menu_type', 'mobile')
            ->with('parent')
            ->orderBy('order')
            ->get();

        return Inertia::render('Admin/Content/StorefrontMenus/Index', [
            'headerMenuItems' => $headerMenuItems,
            'footerMenuItems' => $footerMenuItems,
            'mobileMenuItems' => $mobileMenuItems,
        ]);
    }

    /**
     * Store a new menu item.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'key' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'menu_type' => 'required|string|in:header,footer,mobile',
            'parent_id' => 'nullable|integer|exists:menu_items,id',
            'order' => 'required|integer|min:0',
            'active' => 'boolean',
        ]);

        MenuItem::create([
            ...$validated,
            'location' => 'storefront',
        ]);

        return redirect()->back()->with('success', 'Menu item created successfully.');
    }

    /**
     * Update an existing menu item.
     */
    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        // Ensure we're only updating storefront menus
        if ($menuItem->location !== 'storefront') {
            return redirect()->back()->with('error', 'Invalid menu item.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'key' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'menu_type' => 'required|string|in:header,footer,mobile',
            'parent_id' => 'nullable|integer|exists:menu_items,id',
            'order' => 'required|integer|min:0',
            'active' => 'boolean',
        ]);

        $menuItem->update($validated);

        return redirect()->back()->with('success', 'Menu item updated successfully.');
    }

    /**
     * Delete a menu item.
     */
    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        // Ensure we're only deleting storefront menus
        if ($menuItem->location !== 'storefront') {
            return redirect()->back()->with('error', 'Invalid menu item.');
        }

        // Delete children first
        $menuItem->children()->delete();
        
        // Delete the item
        $menuItem->delete();

        return redirect()->back()->with('success', 'Menu item deleted successfully.');
    }

    /**
     * Reorder menu items.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:menu_items,id',
            'items.*.order' => 'required|integer|min:0',
            'items.*.parent_id' => 'nullable|integer|exists:menu_items,id',
        ]);

        foreach ($validated['items'] as $item) {
            MenuItem::where('id', $item['id'])
                ->where('location', 'storefront')
                ->update([
                    'order' => $item['order'],
                    'parent_id' => $item['parent_id'],
                ]);
        }

        return redirect()->back()->with('success', 'Menu items reordered successfully.');
    }

    /**
     * Toggle menu item active status.
     */
    public function toggle(MenuItem $menuItem): RedirectResponse
    {
        // Ensure we're only toggling storefront menus
        if ($menuItem->location !== 'storefront') {
            return redirect()->back()->with('error', 'Invalid menu item.');
        }

        $menuItem->update(['active' => !$menuItem->active]);

        return redirect()->back()->with('success', 'Menu item status updated.');
    }
}
