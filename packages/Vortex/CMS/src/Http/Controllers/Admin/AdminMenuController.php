<?php

declare(strict_types=1);

namespace Vortex\CMS\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;
use Vortex\CMS\Models\AdminMenuItem;
use Vortex\CMS\Services\AdminMenuService;

class AdminMenuController extends Controller
{
    public function __construct(
        private AdminMenuService $menuService
    ) {}

    /**
     * Display the admin menu manager
     */
    public function index(): InertiaResponse
    {
        $menuItems = $this->menuService->getAllMenuItems();

        return Inertia::render('Admin/System/Menus/Index', [
            'menuItems' => $menuItems,
            'parentOptions' => $this->menuService->getParentOptions(),
        ]);
    }

    /**
     * Store a new menu item
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:admin_menu_items,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        try {
            $this->menuService->createMenuItem($validated);

            return redirect()
                ->back()
                ->with('success', 'Menu item created successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update menu item
     */
    public function update(Request $request, AdminMenuItem $adminMenu): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:admin_menu_items,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        try {
            $this->menuService->updateMenuItem($adminMenu, $validated);

            return redirect()
                ->back()
                ->with('success', 'Menu item updated successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete menu item
     */
    public function destroy(AdminMenuItem $adminMenu): RedirectResponse
    {
        try {
            $this->menuService->deleteMenuItem($adminMenu);

            return redirect()
                ->back()
                ->with('success', 'Menu item deleted successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reorder menu items
     */
    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:admin_menu_items,id',
            'items.*.sort_order' => 'required|integer',
            'items.*.parent_id' => 'nullable|exists:admin_menu_items,id',
        ]);

        try {
            $this->menuService->reorderMenuItems($validated['items']);

            return redirect()
                ->back()
                ->with('success', 'Menu items reordered successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle menu item active status
     */
    public function toggleActive(AdminMenuItem $adminMenu): RedirectResponse
    {
        try {
            $this->menuService->toggleActive($adminMenu);

            return redirect()
                ->back()
                ->with('success', 'Menu item status updated');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
