<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\CMS\Models\Page;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;
use Cartxis\UIEditor\Services\LayoutService;

class PublishPageLayoutTool implements McpToolInterface
{
    public function __construct(
        protected LayoutService $layouts
    ) {}

    public function name(): string
    {
        return 'cartxis_publish_page_layout';
    }

    public function description(): string
    {
        return 'Publish the current UI Editor layout draft for a CMS page or the homepage (make it live on the storefront).';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'page_id' => ['type' => 'integer'],
                'homepage' => ['type' => 'boolean'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $homepage = (bool) ($arguments['homepage'] ?? false);
        $pageId = isset($arguments['page_id']) ? (int) $arguments['page_id'] : null;

        if (! $homepage && ! $pageId) {
            return McpResponse::error('Provide page_id or set homepage=true');
        }

        if ($homepage) {
            $layout = $this->layouts->getHomepage();
        } else {
            $page = Page::query()->find($pageId);
            if (! $page) {
                return McpResponse::error('Page not found');
            }
            $layout = $page->is_homepage
                ? $this->layouts->getHomepage()
                : $this->layouts->getForPage($page);
        }

        if (! $layout) {
            return McpResponse::error('No layout draft found to publish');
        }

        $layout = $this->layouts->publish($layout);

        return McpResponse::text([
            'message' => 'Layout published',
            'layout' => [
                'id' => $layout->id,
                'page_type' => $layout->page_type,
                'status' => $layout->status,
                'published_at' => optional($layout->published_at)?->toIso8601String(),
            ],
        ]);
    }
}
