<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Core\Models\Theme;
use Cartxis\Core\Services\ThemeService;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;

class GetTemplateTool implements McpToolInterface
{
    public function __construct(
        protected ThemeService $themeService
    ) {}

    public function name(): string
    {
        return 'cartxis_get_template';
    }

    public function description(): string
    {
        return 'Get details for an installed storefront template/theme by slug, including theme.json config when available.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'slug' => [
                    'type' => 'string',
                    'description' => 'Template slug (e.g. cartxis-default)',
                ],
            ],
            'required' => ['slug'],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $slug = trim((string) ($arguments['slug'] ?? ''));
        if ($slug === '') {
            return McpResponse::error('slug is required');
        }

        $theme = Theme::query()->where('slug', $slug)->first();
        if (! $theme) {
            return McpResponse::error("Template not found: {$slug}");
        }

        $config = [];
        try {
            $config = $theme->getConfig() ?? [];
        } catch (\Throwable) {
            $config = [];
        }

        $schema = [];
        try {
            $schema = $this->themeService->getSettingsSchema($theme);
        } catch (\Throwable) {
            $schema = [];
        }

        return McpResponse::text([
            'id' => $theme->id,
            'name' => $theme->name,
            'slug' => $theme->slug,
            'description' => $theme->description,
            'category' => $theme->category,
            'version' => $theme->version,
            'author' => $theme->author,
            'author_url' => $theme->author_url,
            'is_active' => (bool) $theme->is_active,
            'is_default' => (bool) $theme->is_default,
            'source' => $theme->source,
            'exists_on_disk' => $theme->exists(),
            'path' => $theme->exists() ? $theme->getPath() : null,
            'settings' => $theme->settings ?? [],
            'settings_schema' => $schema,
            'config' => $config,
        ]);
    }
}
