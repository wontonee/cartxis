<?php

declare(strict_types=1);

namespace Cartxis\Core\Services;

use Cartxis\Core\Support\ThemeDirectoryUrl;
use Illuminate\Support\Facades\Http;
use Throwable;

class ThemeDirectoryRegistrationService
{
    public function register(string $appName, string $appUrl): ?string
    {
        return $this->registerWithDiagnostics($appName, $appUrl)['key'];
    }

    /**
     * @return array{key: ?string, url: string, status: ?int, error: ?string}
     */
    public function registerWithDiagnostics(string $appName, string $appUrl): array
    {
        $directoryUrl = ThemeDirectoryUrl::resolve();

        try {
            $response = Http::acceptJson()
                ->timeout(20)
                ->post($directoryUrl.'/theme-keys/register', [
                    'name' => 'Cartxis install: '.$appName,
                    'app_url' => $appUrl,
                ]);
        } catch (Throwable $exception) {
            return [
                'key' => null,
                'url' => $directoryUrl,
                'status' => null,
                'error' => $exception->getMessage(),
            ];
        }

        if (! $response->successful()) {
            $message = (string) ($response->json('message') ?: $response->body());

            return [
                'key' => null,
                'url' => $directoryUrl,
                'status' => $response->status(),
                'error' => trim($message) !== '' ? trim($message) : 'Request failed.',
            ];
        }

        $key = $response->json('plain_text_key');

        return [
            'key' => is_string($key) && $key !== '' ? $key : null,
            'url' => $directoryUrl,
            'status' => $response->status(),
            'error' => is_string($key) && $key !== '' ? null : 'Directory API did not return a plain_text_key.',
        ];
    }
}
