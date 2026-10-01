<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;
use Cartxis\UIEditor\Services\BlockRegistry;

class ListEditorBlocksTool implements McpToolInterface
{
    public function __construct(
        protected BlockRegistry $blocks
    ) {}

    public function name(): string
    {
        return 'cartxis_list_editor_blocks';
    }

    public function description(): string
    {
        return 'List Cartxis UI Editor / template editor block types (hero, products_grid, text, …) with defaults — use when building layout_data.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'category' => [
                    'type' => 'string',
                    'description' => 'Optional category filter (e.g. layout, content, catalog, marketing)',
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $category = isset($arguments['category']) ? trim((string) $arguments['category']) : '';

        $all = collect($this->blocks->all())
            ->map(fn (array $def) => [
                'type' => $def['type'],
                'label' => $def['label'] ?? $def['type'],
                'category' => $def['category'] ?? 'other',
                'icon' => $def['icon'] ?? null,
                'defaults' => $def['defaults'] ?? [],
            ])
            ->values();

        if ($category !== '') {
            $all = $all->where('category', $category)->values();
        }

        return McpResponse::text([
            'blocks' => $all->all(),
            'categories' => $all->pluck('category')->unique()->values()->all(),
            'count' => $all->count(),
            'hint' => 'layout_data.sections[].columns[].blocks[] items use { id?, type, settings }. Prefer defaults from this list as starting settings.',
        ]);
    }
}
