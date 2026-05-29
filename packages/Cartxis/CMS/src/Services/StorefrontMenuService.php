<?php

declare(strict_types=1);

namespace Cartxis\CMS\Services;

use Cartxis\Core\Models\MenuItem;
use Cartxis\Core\Models\Theme;
use Illuminate\Database\Eloquent\Builder;

class StorefrontMenuService
{
    /**
     * @return array{header: array<int, array>, footer: array<int, array>, mobile: array<int, array>}
     */
    public function getAllMenus(): array
    {
        return [
            'header' => $this->getMenuItems('header'),
            'footer' => $this->getMenuItems('footer'),
            'mobile' => $this->getMenuItems('mobile'),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMenuItems(string $type): array
    {
        return $this->menuQuery($type)
            ->get()
            ->map(fn (MenuItem $item) => $this->formatMenuItem($item))
            ->values()
            ->all();
    }

    private function menuQuery(string $type): Builder
    {
        $query = MenuItem::query()
            ->where('location', 'storefront')
            ->where('menu_type', $type)
            ->where('active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($childQuery) {
                $childQuery->where('active', true)->orderBy('order');
            }])
            ->orderBy('order');

        $activeTheme = Theme::active();
        $themeSlug = $activeTheme?->slug;

        if ($themeSlug && $this->themeHasMenus($themeSlug, $type)) {
            return $query->where('key', 'like', "{$themeSlug}-%");
        }

        $themeSlugs = Theme::query()->pluck('slug')->filter()->all();

        if ($themeSlugs !== []) {
            $query->where(function (Builder $scoped) use ($themeSlugs) {
                foreach ($themeSlugs as $slug) {
                    $scoped->where('key', 'not like', "{$slug}-%");
                }
            });
        }

        return $query;
    }

    private function themeHasMenus(string $themeSlug, string $type): bool
    {
        return MenuItem::query()
            ->where('location', 'storefront')
            ->where('menu_type', $type)
            ->where('active', true)
            ->whereNull('parent_id')
            ->where('key', 'like', "{$themeSlug}-%")
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMenuItem(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'url' => $item->url,
            'route' => $item->route,
            'icon' => $item->icon,
            'children' => $item->children
                ->map(fn (MenuItem $child) => $this->formatMenuItem($child))
                ->values()
                ->all(),
        ];
    }
}
