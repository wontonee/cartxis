<?php

declare(strict_types=1);

namespace Cartxis\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Cartxis\Core\Models\MenuItem;

class MenuController extends Controller
{
    /**
     * Get storefront menu items by type.
     */
    public function getMenu(string $type): JsonResponse
    {
        $validTypes = ['header', 'footer', 'mobile'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json([
                'error' => 'Invalid menu type'
            ], 400);
        }

        $menuItems = MenuItem::where('location', 'storefront')
            ->where('menu_type', $type)
            ->where('active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->where('active', true)->orderBy('order');
            }])
            ->orderBy('order')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'url' => $item->url,
                    'route' => $item->route,
                    'icon' => $item->icon,
                    'children' => $item->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'title' => $child->title,
                            'url' => $child->url,
                            'route' => $child->route,
                            'icon' => $child->icon,
                        ];
                    }),
                ];
            });

        return response()->json([
            'menu_type' => $type,
            'items' => $menuItems,
        ]);
    }

    /**
     * Get all storefront menus.
     */
    public function getAllMenus(): JsonResponse
    {
        $menus = [
            'header' => $this->getMenuItems('header'),
            'footer' => $this->getMenuItems('footer'),
            'mobile' => $this->getMenuItems('mobile'),
        ];

        return response()->json($menus);
    }

    /**
     * Helper to get menu items.
     */
    private function getMenuItems(string $type): array
    {
        return MenuItem::where('location', 'storefront')
            ->where('menu_type', $type)
            ->where('active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->where('active', true)->orderBy('order');
            }])
            ->orderBy('order')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'url' => $item->url,
                    'route' => $item->route,
                    'icon' => $item->icon,
                    'children' => $item->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'title' => $child->title,
                            'url' => $child->url,
                            'route' => $child->route,
                            'icon' => $child->icon,
                        ];
                    })->toArray(),
                ];
            })
            ->toArray();
    }
}
