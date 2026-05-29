<?php

declare(strict_types=1);

namespace Cartxis\CMS\Services;

use Cartxis\Product\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StorefrontMenuSyncService
{
    /**
     * Sync header category dropdown items from the categories table.
     * Safe to call after demo import or whenever categories change.
     */
    public function syncCategoryMenuItems(int $limit = 8): int
    {
        if (! Schema::hasTable('categories') || ! Schema::hasTable('menu_items')) {
            return 0;
        }

        $parent = DB::table('menu_items')
            ->where('key', 'categories')
            ->where('location', 'storefront')
            ->first();

        if (! $parent) {
            return 0;
        }

        $categories = Category::query()
            ->enabled()
            ->root()
            ->showInMenu()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'slug']);

        if ($categories->isEmpty()) {
            return 0;
        }

        $activeKeys = [];

        foreach ($categories as $index => $category) {
            $key = 'category-' . $category->slug;
            $activeKeys[] = $key;

            $existing = DB::table('menu_items')
                ->where('key', $key)
                ->where('location', 'storefront')
                ->first();

            $data = [
                'title'      => $category->name,
                'icon'       => null,
                'route'      => null,
                'url'        => '/category/' . $category->slug,
                'menu_type'  => 'header',
                'parent_id'  => $parent->id,
                'order'      => $index + 1,
                'active'     => true,
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('menu_items')->where('id', $existing->id)->update($data);
            } else {
                DB::table('menu_items')->insert(array_merge($data, [
                    'key'        => $key,
                    'location'   => 'storefront',
                    'created_at' => now(),
                ]));
            }
        }

        // Deactivate stale hardcoded retail category links that no longer exist in the catalog
        DB::table('menu_items')
            ->where('location', 'storefront')
            ->where('parent_id', $parent->id)
            ->whereNotIn('key', $activeKeys)
            ->update(['active' => false, 'updated_at' => now()]);

        return $categories->count();
    }

    /**
     * Ensure deals menu items point to the on-sale products listing.
     */
    public function fixDealsUrls(): void
    {
        if (! Schema::hasTable('menu_items')) {
            return;
        }

        DB::table('menu_items')
            ->whereIn('key', ['deals', 'mobile-deals'])
            ->where('location', 'storefront')
            ->update(['url' => '/products?on_sale=1', 'updated_at' => now()]);
    }
}
