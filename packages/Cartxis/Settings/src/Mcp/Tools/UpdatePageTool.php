<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\CMS\Models\Page;
use Cartxis\CMS\Services\PageService;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdatePageTool implements McpToolInterface
{
    public function __construct(
        protected PageService $pageService
    ) {}

    public function name(): string
    {
        return 'cartxis_update_page';
    }

    public function description(): string
    {
        return 'Update CMS page metadata (title, url_key, status, SEO). For layout/blocks use cartxis_update_page_layout.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'url_key' => ['type' => 'string'],
                'status' => [
                    'type' => 'string',
                    'enum' => ['draft', 'published', 'disabled'],
                ],
                'meta_title' => ['type' => 'string'],
                'meta_description' => ['type' => 'string'],
                'meta_keywords' => ['type' => 'string'],
            ],
            'required' => ['id'],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $page = Page::query()->find((int) ($arguments['id'] ?? 0));
        if (! $page) {
            return McpResponse::error('Page not found');
        }

        try {
            $validated = Validator::make($arguments, [
                'id' => 'required|integer',
                'title' => 'sometimes|string|max:255',
                'url_key' => 'sometimes|nullable|string|max:255',
                'status' => 'sometimes|in:draft,published,disabled',
                'meta_title' => 'sometimes|nullable|string|max:255',
                'meta_description' => 'sometimes|nullable|string|max:500',
                'meta_keywords' => 'sometimes|nullable|string|max:255',
            ])->validate();
        } catch (ValidationException $e) {
            return McpResponse::error(json_encode($e->errors()));
        }

        unset($validated['id']);
        $page = $this->pageService->update($page, $validated);

        return McpResponse::text([
            'message' => 'Page updated',
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'url_key' => $page->url_key,
                'status' => $page->status,
                'is_homepage' => (bool) $page->is_homepage,
                'editor_url' => url('/admin/uieditor/pages/'.$page->id.'/editor'),
            ],
        ]);
    }
}
