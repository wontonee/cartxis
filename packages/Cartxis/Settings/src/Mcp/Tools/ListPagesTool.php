<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\CMS\Models\Page;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;

class ListPagesTool implements McpToolInterface
{
    public function name(): string
    {
        return 'cartxis_list_pages';
    }

    public function description(): string
    {
        return 'List CMS pages (Content → Pages) that can be edited in the Cartxis UI / template editor.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => ['type' => 'string'],
                'status' => [
                    'type' => 'string',
                    'enum' => ['draft', 'published', 'disabled'],
                ],
                'page' => ['type' => 'integer', 'minimum' => 1],
                'per_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50],
            ],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $pageNum = max(1, (int) ($arguments['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($arguments['per_page'] ?? 20)));
        $search = isset($arguments['search']) ? trim((string) $arguments['search']) : '';
        $status = isset($arguments['status']) ? (string) $arguments['status'] : '';

        $query = Page::query()->orderByRaw('is_homepage DESC')->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('url_key', 'like', "%{$search}%");
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $pageNum);

        return McpResponse::text([
            'data' => $paginator->getCollection()->map(fn (Page $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'url_key' => $p->url_key,
                'status' => $p->status,
                'is_homepage' => (bool) $p->is_homepage,
                'editor_url' => url('/admin/uieditor/pages/'.$p->id.'/editor'),
                'updated_at' => optional($p->updated_at)?->toIso8601String(),
            ])->values()->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
