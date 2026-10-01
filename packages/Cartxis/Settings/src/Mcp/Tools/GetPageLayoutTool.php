<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\CMS\Models\Page;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;
use Cartxis\UIEditor\Services\LayoutService;

class GetPageLayoutTool implements McpToolInterface
{
    public function __construct(
        protected LayoutService $layouts
    ) {}

    public function name(): string
    {
        return 'cartxis_get_page_layout';
    }

    public function description(): string
    {
        return 'Get Cartxis UI Editor layout_data (sections → columns → blocks) for a CMS page or the homepage.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'page_id' => [
                    'type' => 'integer',
                    'description' => 'CMS page id (required unless homepage=true)',
                ],
                'homepage' => [
                    'type' => 'boolean',
                    'description' => 'Load the store homepage layout instead of a CMS page',
                ],
                'published_only' => [
                    'type' => 'boolean',
                    'description' => 'Return only the published layout (default false = current draft/published)',
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $homepage = (bool) ($arguments['homepage'] ?? false);
        $publishedOnly = (bool) ($arguments['published_only'] ?? false);
        $pageId = isset($arguments['page_id']) ? (int) $arguments['page_id'] : null;

        if (! $homepage && ! $pageId) {
            return McpResponse::error('Provide page_id or set homepage=true');
        }

        if ($homepage) {
            $layout = $publishedOnly
                ? $this->layouts->getPublishedHomepage()
                : $this->layouts->getHomepage();
            $pageMeta = ['homepage' => true];
        } else {
            $page = Page::query()->find($pageId);
            if (! $page) {
                return McpResponse::error('Page not found');
            }

            if ($page->is_homepage) {
                $layout = $publishedOnly
                    ? $this->layouts->getPublishedHomepage()
                    : $this->layouts->getHomepage();
            } else {
                $layout = $publishedOnly
                    ? $this->layouts->getPublishedForPage($page)
                    : $this->layouts->getForPage($page);
            }

            $pageMeta = [
                'page_id' => $page->id,
                'title' => $page->title,
                'url_key' => $page->url_key,
                'is_homepage' => (bool) $page->is_homepage,
                'editor_url' => url('/admin/uieditor/pages/'.$page->id.'/editor'),
            ];
        }

        if (! $layout) {
            return McpResponse::text([
                'message' => 'No layout found',
                'page' => $pageMeta,
                'layout' => null,
                'layout_data' => $this->layouts->emptyLayout(),
            ]);
        }

        return McpResponse::text([
            'page' => $pageMeta,
            'layout' => [
                'id' => $layout->id,
                'page_type' => $layout->page_type,
                'status' => $layout->status,
                'published_at' => optional($layout->published_at)?->toIso8601String(),
            ],
            'layout_data' => $layout->layout_data ?? $this->layouts->emptyLayout(),
        ]);
    }
}
