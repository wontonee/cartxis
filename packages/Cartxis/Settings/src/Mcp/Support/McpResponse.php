<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Support;

final class McpResponse
{
    /**
     * @return array{content: list<array{type: string, text: string}>, isError?: bool}
     */
    public static function text(mixed $data, bool $isError = false): array
    {
        $text = is_string($data)
            ? $data
            : (string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $result = [
            'content' => [
                [
                    'type' => 'text',
                    'text' => $text,
                ],
            ],
        ];

        if ($isError) {
            $result['isError'] = true;
        }

        return $result;
    }

    /**
     * @return array{content: list<array{type: string, text: string}>, isError: bool}
     */
    public static function error(string $message): array
    {
        return self::text(['error' => $message], true);
    }
}
