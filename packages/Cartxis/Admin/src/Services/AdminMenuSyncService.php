<?php

declare(strict_types=1);

namespace Cartxis\Admin\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminMenuSyncService
{
    /**
     * Ensure critical admin navigation items exist with correct hierarchy.
     *
     * Safe to run after migrations, after seeders, or on existing stores upgrading
     * to a release that added menu rows only in seeders (no migration at the time).
     */
    public function sync(): void
    {
        if (! Schema::hasTable('menu_items')) {
            return;
        }

        $marketingId = $this->parentId('marketing');
        $appearanceId = $this->parentId('appearance');

        if ($marketingId) {
            $this->syncMarketingChildren($marketingId);
            $this->reparentOrphanedNewsletterItems($marketingId);
        }

        if ($appearanceId) {
            $this->syncAppearanceTemplateZone($appearanceId);
        }
    }

    private function parentId(string $key): ?int
    {
        $id = DB::table('menu_items')
            ->where('key', $key)
            ->where('location', 'admin')
            ->value('id');

        return $id ? (int) $id : null;
    }

    private function syncMarketingChildren(int $marketingId): void
    {
        $children = [
            [
                'key' => 'marketing-coupons',
                'title' => 'Coupons',
                'icon' => 'ticket',
                'route' => 'admin.marketing.coupons.index',
                'order' => 10,
            ],
            [
                'key' => 'marketing-promotions',
                'title' => 'Promotions',
                'icon' => 'sparkles',
                'route' => 'admin.marketing.promotions.index',
                'order' => 20,
            ],
            [
                'key' => 'marketing-newsletters',
                'title' => 'Newsletters',
                'icon' => 'mail',
                'route' => 'admin.marketing.newsletters.index',
                'order' => 30,
            ],
        ];

        foreach ($children as $child) {
            DB::table('menu_items')->updateOrInsert(
                ['key' => $child['key']],
                [
                    'title' => $child['title'],
                    'icon' => $child['icon'],
                    'route' => $child['route'],
                    'url' => null,
                    'parent_id' => $marketingId,
                    'order' => $child['order'],
                    'permission' => null,
                    'location' => 'admin',
                    'active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    /**
     * Older releases inserted newsletter rows before the Marketing parent existed,
     * leaving a top-level "Newsletters" item (parent_id = null).
     */
    private function reparentOrphanedNewsletterItems(int $marketingId): void
    {
        DB::table('menu_items')
            ->where('location', 'admin')
            ->whereNull('parent_id')
            ->where('route', 'admin.marketing.newsletters.index')
            ->where('key', '!=', 'marketing')
            ->update([
                'parent_id' => $marketingId,
                'active' => true,
                'order' => 30,
                'updated_at' => now(),
            ]);

        // Remove duplicate top-level newsletter links that are not the canonical key.
        DB::table('menu_items')
            ->where('location', 'admin')
            ->whereNull('parent_id')
            ->where('key', '!=', 'marketing-newsletters')
            ->where(function ($query) {
                $query->where('route', 'admin.marketing.newsletters.index')
                    ->orWhere(function ($inner) {
                        $inner->whereRaw('LOWER(title) = ?', ['newsletters'])
                            ->where('route', 'like', '%newsletter%');
                    });
            })
            ->delete();
    }

    private function syncAppearanceTemplateZone(int $appearanceId): void
    {
        DB::table('menu_items')->updateOrInsert(
            ['key' => 'appearance-template-zone'],
            [
                'title' => 'Browse Themes',
                'icon' => 'layout-template',
                'route' => 'admin.template-zone.index',
                'url' => null,
                'parent_id' => $appearanceId,
                'order' => 0,
                'permission' => null,
                'location' => 'admin',
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
