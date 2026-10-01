<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\CMS\Models\Page;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;
use Cartxis\UIEditor\Models\PageLayout;
use Cartxis\UIEditor\Services\LayoutService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdatePageLayoutTool implements McpToolInterface
{
    public function __construct(
        protected LayoutService $layouts
    ) {}

    public function name(): string
    {
        return 'cartxis_update_page_layout';
    }

    public function description(): string
    {
        return 'Save a Cartxis UI Editor layout draft (sections → columns → blocks). Optionally publish. Same schema as Admin → Content → Pages → Editor.';
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
                    'description' => 'Edit the store homepage layout',
                ],
                'layout_data' => [
                    'type' => 'object',
                    'description' => 'UI Editor JSON: { version, sections: [{ columns: [{ blocks: [...] }] }] }',
                    'properties' => [
                        'version' => ['type' => 'string'],
                        'sections' => ['type' => 'array'],
                    ],
                    'required' => ['sections'],
                ],
                'publish' => [
                    'type' => 'boolean',
                    'description' => 'Publish immediately after saving draft (default false)',
                ],
            ],
            'required' => ['layout_data'],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        try {
            $validated = Validator::make($arguments, [
                'page_id' => 'nullable|integer',
                'homepage' => 'nullable|boolean',
                'layout_data' => 'required|array',
                'layout_data.sections' => 'required|array',
                'layout_data.version' => 'nullable|string',
                'publish' => 'nullable|boolean',
            ])->validate();
        } catch (ValidationException $e) {
            return McpResponse::error(json_encode($e->errors()));
        }

        $homepage = (bool) ($validated['homepage'] ?? false);
        $pageId = isset($validated['page_id']) ? (int) $validated['page_id'] : null;
        $layoutData = $validated['layout_data'];
        $layoutData['version'] = $layoutData['version'] ?? '2.0';
        $publish = (bool) ($validated['publish'] ?? false);

        if (! $homepage && ! $pageId) {
            return McpResponse::error('Provide page_id or set homepage=true');
        }

        if ($homepage) {
            $pageType = PageLayout::TYPE_HOMEPAGE;
            $resolvedPageId = null;
            $pageMeta = ['homepage' => true];
        } else {
            $page = Page::query()->find($pageId);
            if (! $page) {
                return McpResponse::error('Page not found');
            }

            if ($page->is_homepage) {
                $pageType = PageLayout::TYPE_HOMEPAGE;
                $resolvedPageId = null;
            } else {
                $pageType = PageLayout::TYPE_CMS_PAGE;
                $resolvedPageId = $page->id;
            }

            $pageMeta = [
                'page_id' => $page->id,
                'title' => $page->title,
                'url_key' => $page->url_key,
                'is_homepage' => (bool) $page->is_homepage,
                'editor_url' => url('/admin/uieditor/pages/'.$page->id.'/editor'),
            ];
        }

        $layout = $this->layouts->saveDraft($layoutData, $pageType, $resolvedPageId);

        if ($publish) {
            $layout = $this->layouts->publish($layout);
        }

        return McpResponse::text([
            'message' => $publish ? 'Layout saved and published' : 'Layout draft saved',
            'page' => $pageMeta,
            'layout' => [
                'id' => $layout->id,
                'page_type' => $layout->page_type,
                'status' => $layout->status,
                'published_at' => optional($layout->published_at)?->toIso8601String(),
                'section_count' => is_array($layout->layout_data['sections'] ?? null)
                    ? count($layout->layout_data['sections'])
                    : 0,
            ],
        ]);
    }
}
