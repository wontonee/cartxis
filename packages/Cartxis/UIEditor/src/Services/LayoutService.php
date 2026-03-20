<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Services;

use Cartxis\UIEditor\Models\PageLayout;
use Cartxis\UIEditor\Repositories\LayoutRepository;
use Cartxis\CMS\Models\Page;
use Mews\Purifier\Facades\Purifier;

class LayoutService
{
    public function __construct(
        protected LayoutRepository $repository
    ) {}

    /**
     * Get the current layout (draft or published) for a CMS page.
     */
    public function getForPage(Page $page): ?PageLayout
    {
        return $this->repository->findForPage($page);
    }

    /**
     * Get the published layout for a CMS page (for storefront rendering).
     */
    public function getPublishedForPage(Page $page): ?PageLayout
    {
        return $this->repository->findPublishedForPage($page);
    }

    /**
     * Get the current homepage layout.
     */
    public function getHomepage(): ?PageLayout
    {
        return $this->repository->findHomepage();
    }

    /**
     * Get the published homepage layout (for storefront rendering).
     */
    public function getPublishedHomepage(): ?PageLayout
    {
        return $this->repository->findPublishedHomepage();
    }

    /**
     * Save (upsert) the layout as a draft.
     * Never changes a published layout's status to published from here.
     */
    public function saveDraft(array $layoutData, string $pageType, ?int $pageId = null): PageLayout
    {
        $existing = match ($pageType) {
            PageLayout::TYPE_CMS_PAGE => $this->repository->findForPage(
                \Cartxis\CMS\Models\Page::findOrFail($pageId)
            ),
            PageLayout::TYPE_HOMEPAGE => $this->repository->findHomepage(),
            default                   => null,
        };

        $layoutData = $this->sanitizeLayoutBlocks($layoutData);

        $payload = [
            'layout_data' => $layoutData,
            'status'      => PageLayout::STATUS_DRAFT,
        ];

        if ($existing) {
            return $this->repository->update($existing, $payload);
        }

        return $this->repository->create(array_merge($payload, [
            'page_type' => $pageType,
            'page_id'   => $pageType === PageLayout::TYPE_CMS_PAGE ? $pageId : null,
        ]));
    }

    /**
     * Publish the given layout, making it live on the storefront.
     */
    public function publish(PageLayout $layout): PageLayout
    {
        return $this->repository->update($layout, [
            'status'       => PageLayout::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    /**
     * Revert a published layout back to draft, removing it from the storefront.
     */
    public function unpublish(PageLayout $layout): PageLayout
    {
        return $this->repository->update($layout, [
            'status'       => PageLayout::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }

    /**
     * Build the initial empty layout structure for a new editor session.
     */
    public function emptyLayout(): array
    {
        return [
            'version'  => '1.0',
            'sections' => [],
        ];
    }

    /**
     * Sanitize HTML content in UIEditor block settings to prevent stored XSS.
     * Walks the layout structure and cleans `content` fields for block types
     * that render raw HTML on the storefront via v-html.
     */
    private function sanitizeLayoutBlocks(array $layoutData): array
    {
        if (empty($layoutData['sections']) || !is_array($layoutData['sections'])) {
            return $layoutData;
        }

        foreach ($layoutData['sections'] as $si => $section) {
            if (!empty($section['blocks']) && is_array($section['blocks'])) {
                foreach ($section['blocks'] as $bi => $block) {
                    $layoutData['sections'][$si]['blocks'][$bi] = $this->sanitizeBlock($block);
                }
            }
            // Handle column-based layouts where blocks live inside columns
            if (!empty($section['columns']) && is_array($section['columns'])) {
                foreach ($section['columns'] as $ci => $column) {
                    if (!empty($column['blocks']) && is_array($column['blocks'])) {
                        foreach ($column['blocks'] as $bi => $block) {
                            $layoutData['sections'][$si]['columns'][$ci]['blocks'][$bi] = $this->sanitizeBlock($block);
                        }
                    }
                }
            }
        }

        return $layoutData;
    }

    private function sanitizeBlock(array $block): array
    {
        $settings = $block['settings'] ?? [];

        switch ($block['type'] ?? '') {
            case 'html':
            case 'text':
                if (isset($settings['content'])) {
                    $settings['content'] = Purifier::clean($settings['content']);
                }
                break;
            case 'tabs':
                if (!empty($settings['tabs']) && is_array($settings['tabs'])) {
                    foreach ($settings['tabs'] as $ti => $tab) {
                        if (isset($tab['content'])) {
                            $settings['tabs'][$ti]['content'] = Purifier::clean($tab['content']);
                        }
                    }
                }
                break;
            case 'accordion':
                if (!empty($settings['items']) && is_array($settings['items'])) {
                    foreach ($settings['items'] as $ii => $item) {
                        if (isset($item['content'])) {
                            $settings['items'][$ii]['content'] = Purifier::clean($item['content']);
                        }
                    }
                }
                break;
        }

        $block['settings'] = $settings;
        return $block;
    }
}
