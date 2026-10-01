<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\CMS\Models\Page;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;
use Cartxis\UIEditor\Services\LayoutService;

class GetPageTool implements McpToolInterface
{
    public function __construct(
        protected LayoutService $layouts
    ) {}

    public function name(): string
    {
        return 'cartxis_get_page';
    }

    public function description(): string
    {
        return 'Get a CMS page by id or url_key, including layout status and UI Editor URL.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'url_key' => ['type' => 'string'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $id = $arguments['id'] ?? null;
        $urlKey = isset($arguments['url_key']) ? trim((string) $arguments['url_key']) : '';

        if (! $id && $urlKey === '') {
            return McpResponse::error('Provide id or url_key');
        }

        $page = $id
            ? Page::query()->find((int) $id)
            : Page::query()->where('url_key', $urlKey)->first();

        if (! $page) {
            return McpResponse::error('Page not found');
        }

        $layout = $page->is_homepage
            ? $this->layouts->getHomepage()
            : $this->layouts->getForPage($page);

        return McpResponse::text([
            'id' => $page->id,
            'title' => $page->title,
            'url_key' => $page->url_key,
            'status' => $page->status,
            'is_homepage' => (bool) $page->is_homepage,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'meta_keywords' => $page->meta_keywords,
            'editor_url' => url('/admin/uieditor/pages/'.$page->id.'/editor'),
            'layout' => $layout ? [
                'id' => $layout->id,
                'page_type' => $layout->page_type,
                'status' => $layout->status,
                'published_at' => optional($layout->published_at)?->toIso8601String(),
                'has_sections' => is_array($layout->layout_data['sections'] ?? null)
                    ? count($layout->layout_data['sections'])
                    : 0,
            ] : null,
            'created_at' => optional($page->created_at)?->toIso8601String(),
            'updated_at' => optional($page->updated_at)?->toIso8601String(),
        ]);
    }
}
