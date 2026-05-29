<?php

declare(strict_types=1);

namespace Cartxis\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Cartxis\CMS\Services\StorefrontMenuService;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function __construct(
        protected StorefrontMenuService $menuService
    ) {}

    /**
     * Get storefront menu items by type.
     */
    public function getMenu(string $type): JsonResponse
    {
        $validTypes = ['header', 'footer', 'mobile'];

        if (! in_array($type, $validTypes)) {
            return response()->json([
                'error' => 'Invalid menu type',
            ], 400);
        }

        return response()->json([
            'menu_type' => $type,
            'items' => $this->menuService->getMenuItems($type),
        ]);
    }

    /**
     * Get all storefront menus.
     */
    public function getAllMenus(): JsonResponse
    {
        return response()->json($this->menuService->getAllMenus());
    }
}
