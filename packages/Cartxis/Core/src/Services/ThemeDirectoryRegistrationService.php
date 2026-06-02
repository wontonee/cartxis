<?php

declare(strict_types=1);

namespace Cartxis\Core\Services;

use Illuminate\Support\Facades\Http;

class ThemeDirectoryRegistrationService
{
    public function register(string $appName, string $appUrl): ?string
    {
        $directoryUrl = rtrim((string) config('theme.directory.url', ''), '/');

        if ($directoryUrl === '') {
            return null;
        }

        $response = Http::acceptJson()
            ->timeout(20)
            ->post($directoryUrl.'/theme-keys/register', [
                'name' => 'Cartxis install: '.$appName,
                'app_url' => $appUrl,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $key = $response->json('plain_text_key');

        return is_string($key) && $key !== '' ? $key : null;
    }
}
