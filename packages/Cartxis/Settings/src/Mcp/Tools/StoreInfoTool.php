<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Core\Models\Currency;
use Cartxis\Core\Models\Locale;
use Cartxis\Core\Services\SettingService;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;

class StoreInfoTool implements McpToolInterface
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function name(): string
    {
        return 'cartxis_store_info';
    }

    public function description(): string
    {
        return 'Get store name, URL, and currency/locale summary from Cartxis settings.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => new \stdClass,
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $locale = Locale::query()->where('is_default', true)->first();
        $currency = Currency::query()->where('is_default', true)->first();

        return McpResponse::text([
            'store_name' => (string) $this->settingService->get('store_name', $this->settingService->get('site_name', '')),
            'site_name' => (string) $this->settingService->get('site_name', ''),
            'site_tagline' => (string) $this->settingService->get('site_tagline', ''),
            'url' => (string) config('app.url'),
            'store_email' => (string) $this->settingService->get('store_email', ''),
            'store_country' => (string) $this->settingService->get('store_country', ''),
            'store_timezone' => (string) $this->settingService->get('store_timezone', 'UTC'),
            'locale' => $locale ? [
                'code' => $locale->code,
                'name' => $locale->name,
            ] : null,
            'currency' => $currency ? [
                'code' => $currency->code,
                'symbol' => $currency->symbol ?? null,
                'name' => $currency->name,
            ] : null,
            'mcp_enabled' => (bool) $this->settingService->get('mcp.enabled', false),
        ]);
    }
}
