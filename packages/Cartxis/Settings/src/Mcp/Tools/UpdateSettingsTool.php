<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Core\Services\SettingService;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;
use Cartxis\Settings\Mcp\Support\SafeSettings;

class UpdateSettingsTool implements McpToolInterface
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function name(): string
    {
        return 'cartxis_update_settings';
    }

    public function description(): string
    {
        return 'Update a single safe general/store setting by key. Whitelisted keys only — never payment/SMTP/AI secrets.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'key' => [
                    'type' => 'string',
                    'description' => 'Setting key (whitelisted general/store keys only)',
                ],
                'value' => [
                    'description' => 'New value (string, number, or boolean)',
                ],
            ],
            'required' => ['key', 'value'],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $key = (string) ($arguments['key'] ?? '');
        if ($key === '' || ! SafeSettings::isAllowed($key)) {
            return McpResponse::error(
                'Key not allowed. Use a whitelisted general/store key. Never use payment, SMTP, or AI keys.'
            );
        }

        if (! array_key_exists('value', $arguments)) {
            return McpResponse::error('Missing value');
        }

        $value = $arguments['value'];
        $group = SafeSettings::groupForKey($key);

        $type = match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            default => 'string',
        };

        if (in_array($key, ['checkout_allow_guest', 'checkout_require_account'], true)) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($value === null) {
                return McpResponse::error('Value must be boolean for '.$key);
            }
            $type = 'boolean';
        }

        if (is_string($value) && mb_strlen($value) > 5000) {
            return McpResponse::error('Value too long (max 5000 characters)');
        }

        $this->settingService->set($key, $value, $type, $group);

        return McpResponse::text([
            'message' => 'Setting updated',
            'key' => $key,
            'value' => $this->settingService->get($key),
            'group' => $group,
        ]);
    }
}
