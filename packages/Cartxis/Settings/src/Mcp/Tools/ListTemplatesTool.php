<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Core\Services\ThemeService;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;

class ListTemplatesTool implements McpToolInterface
{
    public function __construct(
        protected ThemeService $themeService
    ) {}

    public function name(): string
    {
        return 'cartxis_list_templates';
    }

    public function description(): string
    {
        return 'List installed storefront templates/themes. Optionally rediscover from disk and filter to the active template only.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'discover' => [
                    'type' => 'boolean',
                    'description' => 'Run theme discovery before listing (default false)',
                ],
                'active_only' => [
                    'type' => 'boolean',
                    'description' => 'Only return the currently active template',
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        if (! empty($arguments['discover'])) {
            $this->themeService->discover();
        }

        $themes = $this->themeService->all();

        if (! empty($arguments['active_only'])) {
            $themes = $themes->where('is_active', true)->values();
        }

        return McpResponse::text([
            'data' => $themes->map(fn ($theme) => [
                'id' => $theme->id,
                'name' => $theme->name,
                'slug' => $theme->slug,
                'category' => $theme->category,
                'version' => $theme->version,
                'author' => $theme->author,
                'is_active' => (bool) $theme->is_active,
                'is_default' => (bool) $theme->is_default,
                'source' => $theme->source,
                'exists_on_disk' => $theme->exists(),
            ])->values()->all(),
            'count' => $themes->count(),
        ]);
    }
}
