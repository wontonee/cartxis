<?php

declare(strict_types=1);

namespace Cartxis\CMS\Services;

use Cartxis\CMS\Models\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PageService
{
    /**
     * Create a new page.
     */
    public function create(array $data): Page
    {
        return DB::transaction(function () use ($data) {
            // Ensure unique URL key
            $data['url_key'] = $this->generateUniqueSlug(
                $data['title'],
                $data['url_key'] ?? null
            );

            return Page::create($data);
        });
    }

    /**
     * Update an existing page.
     */
    public function update(Page $page, array $data): Page
    {
        DB::transaction(function () use ($page, $data) {
            // Update URL key if title changed
            if (isset($data['title']) && $data['title'] !== $page->title) {
                $data['url_key'] = $this->generateUniqueSlug(
                    $data['title'],
                    $data['url_key'] ?? null,
                    $page->id
                );
            }

            $page->update($data);

            // Clear cache for this page
            $this->clearPageCache($page->url_key);
        });

        return $page->fresh();
    }

    /**
     * Delete a page.
     */
    public function delete(Page $page): bool
    {
        $this->clearPageCache($page->url_key);

        return $page->delete();
    }

    /**
     * Generate unique slug for URL key.
     */
    public function generateUniqueSlug(
        string $title,
        ?string $preferredSlug = null,
        ?int $excludeId = null
    ): string {
        $slug = $preferredSlug ?? Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists.
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Page::where('url_key', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Clear cache for a page.
     */
    protected function clearPageCache(string $urlKey): void
    {
        Cache::forget("page:{$urlKey}");
    }

    /**
     * Bulk update status.
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return Page::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * Bulk delete pages.
     */
    public function bulkDelete(array $ids): int
    {
        $pages = Page::whereIn('id', $ids)->get();

        foreach ($pages as $page) {
            $this->clearPageCache($page->url_key);
        }

        return Page::whereIn('id', $ids)->delete();
    }
}
