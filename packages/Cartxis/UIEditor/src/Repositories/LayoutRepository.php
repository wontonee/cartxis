<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Repositories;

use Cartxis\UIEditor\Models\PageLayout;
use Cartxis\CMS\Models\Page;

class LayoutRepository
{
    /**
     * Get the layout (any status) for a CMS page.
     */
    public function findForPage(Page $page): ?PageLayout
    {
        return PageLayout::forPage($page)->latest()->first();
    }

    /**
     * Get the published layout for a CMS page (storefront use).
     */
    public function findPublishedForPage(Page $page): ?PageLayout
    {
        return PageLayout::forPage($page)->published()->first();
    }

    /**
     * Get the layout (any status) for the homepage.
     */
    public function findHomepage(): ?PageLayout
    {
        return PageLayout::homepage()->latest()->first();
    }

    /**
     * Get the published homepage layout (storefront use).
     */
    public function findPublishedHomepage(): ?PageLayout
    {
        return PageLayout::homepage()->published()->first();
    }

    /**
     * Create a new layout record.
     */
    public function create(array $attributes): PageLayout
    {
        return PageLayout::create($attributes);
    }

    /**
     * Update an existing layout record.
     */
    public function update(PageLayout $layout, array $attributes): PageLayout
    {
        $layout->update($attributes);
        return $layout->fresh();
    }
}
