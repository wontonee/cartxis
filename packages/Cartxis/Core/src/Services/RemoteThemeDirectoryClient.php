<?php

declare(strict_types=1);

namespace Cartxis\Core\Services;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RemoteThemeDirectoryClient
{
    /**
     * @return array<int, array{slug: string, name: string, description?: string|null, icon?: string|null, sort_order?: int}>
     */
    public function fetchCategories(): array
    {
        if (! $this->canBrowse()) {
            return [];
        }

        $cacheKey = $this->cacheKey('categories');

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $response = $this->httpClient()
            ->timeout(15)
            ->get($this->endpoint('theme-categories'));

        if (! $response->successful()) {
            $this->logFetchFailure('theme-categories', $response->status(), $response->body());

            return [];
        }

        $data = $response->json('data');
        $categories = is_array($data) ? $data : [];

        Cache::put($cacheKey, $categories, $this->cacheTtl());

        return $categories;
    }

    /**
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function fetchThemes(?string $category = null, ?string $search = null, int $page = 1, int $perPage = 24): array
    {
        if (! $this->canBrowse()) {
            return ['data' => [], 'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0]];
        }

        $query = array_filter([
            'search' => $search,
            'page' => max(1, $page),
            'per_page' => min(50, max(1, $perPage)),
        ], fn ($value) => $value !== null && $value !== '');

        if ($category !== null && $category !== '') {
            $query['categories'] = [$category];
        }

        $cacheKey = $this->cacheKey('themes:'.md5(json_encode($query) ?: ''));

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $response = $this->httpClient()
            ->timeout(20)
            ->get($this->endpoint('themes'), $query);

        if (! $response->successful()) {
            $this->logFetchFailure('themes', $response->status(), $response->body());

            return ['data' => [], 'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0]];
        }

        $result = [
            'data' => is_array($response->json('data')) ? $response->json('data') : [],
            'meta' => is_array($response->json('meta')) ? $response->json('meta') : [],
        ];

        Cache::put($cacheKey, $result, $this->cacheTtl());

        return $result;
    }

    /**
     * @return array{path: string, hash: string|null}
     */
    public function download(string $slug): array
    {
        if (! $this->canInstall()) {
            throw new Exception('Theme directory install is not configured. Set CARTXIS_THEME_API_KEY in your .env file.');
        }

        $response = $this->httpClient()
            ->withToken($this->apiKey())
            ->timeout(120)
            ->withOptions(['stream' => false])
            ->post($this->endpoint('themes/'.rawurlencode($slug).'/install'));

        if ($response->status() === 401) {
            throw new Exception('Theme directory API key is invalid or revoked. Check CARTXIS_THEME_API_KEY in your .env file.');
        }

        if ($response->status() === 404) {
            throw new Exception('Theme not found in the Cartxis directory.');
        }

        if ($response->status() === 429) {
            throw new Exception('Theme directory rate limit exceeded. Please try again in a minute.');
        }

        if (! $response->successful()) {
            throw new Exception('Unable to download theme package from the Cartxis directory.');
        }

        $expectedHash = $response->header('X-Theme-Hash');
        $tempPath = storage_path('app/temp/remote-theme-'.$slug.'-'.time().'.zip');

        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        file_put_contents($tempPath, $response->body());

        $actualHash = hash_file('sha256', $tempPath) ?: null;

        if ($expectedHash && $actualHash && ! hash_equals($expectedHash, $actualHash)) {
            @unlink($tempPath);

            throw new Exception('Downloaded theme package failed integrity verification.');
        }

        return [
            'path' => $tempPath,
            'hash' => $actualHash,
        ];
    }

    /**
     * Public catalog routes are available when the directory URL is set.
     */
    public function canBrowse(): bool
    {
        return filled($this->baseUrl());
    }

    /**
     * Protected install/download routes require a bearer API key.
     */
    public function canInstall(): bool
    {
        return $this->canBrowse() && filled($this->apiKey());
    }

    /** @deprecated Use canInstall() */
    public function isConfigured(): bool
    {
        return $this->canInstall();
    }

    public function clearCache(): void
    {
        $versionKey = $this->cacheKeyPrefix().'.version';
        Cache::put($versionKey, (int) Cache::get($versionKey, 1) + 1, now()->addDays(30));
    }

    /**
     * Probe the directory API (uncached) — useful for admin diagnostics.
     *
     * @return array{ok: bool, theme_count: int, category_count: int, status: int|null, error: string|null}
     */
    public function probe(): array
    {
        if (! $this->canBrowse()) {
            return [
                'ok' => false,
                'theme_count' => 0,
                'category_count' => 0,
                'status' => null,
                'error' => 'CARTXIS_THEME_DIRECTORY_URL is not configured.',
            ];
        }

        try {
            $categoryResponse = $this->httpClient()
                ->timeout(10)
                ->get($this->endpoint('theme-categories'));

            $themeResponse = $this->httpClient()
                ->timeout(10)
                ->get($this->endpoint('themes'), ['per_page' => 1]);

            if (! $themeResponse->successful()) {
                return [
                    'ok' => false,
                    'theme_count' => 0,
                    'category_count' => 0,
                    'status' => $themeResponse->status(),
                    'error' => 'Theme directory request failed (HTTP '.$themeResponse->status().').',
                ];
            }

            return [
                'ok' => true,
                'theme_count' => (int) $themeResponse->json('meta.total', count($themeResponse->json('data', []))),
                'category_count' => $categoryResponse->successful()
                    ? count($categoryResponse->json('data', []))
                    : 0,
                'status' => $themeResponse->status(),
                'error' => null,
            ];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'theme_count' => 0,
                'category_count' => 0,
                'status' => null,
                'error' => $exception->getMessage(),
            ];
        }
    }

    protected function httpClient(): PendingRequest
    {
        $client = Http::acceptJson();

        if (app()->environment('local', 'testing')) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    protected function logFetchFailure(string $endpoint, int $status, string $body): void
    {
        logger()->warning('Cartxis theme directory fetch failed', [
            'endpoint' => $this->endpoint($endpoint),
            'status' => $status,
            'body' => mb_substr($body, 0, 500),
        ]);
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('theme.directory.url', ''), '/');
    }

    protected function apiKey(): string
    {
        return (string) config('theme.directory.api_key', '');
    }

    protected function cacheTtl(): int
    {
        return max(60, (int) config('theme.directory.cache_ttl', 3600));
    }

    protected function endpoint(string $path): string
    {
        return $this->baseUrl().'/'.ltrim($path, '/');
    }

    protected function cacheKey(string $suffix): string
    {
        $version = (int) Cache::get($this->cacheKeyPrefix().'.version', 1);

        return $this->cacheKeyPrefix().'.v'.$version.'.'.$suffix;
    }

    protected function cacheKeyPrefix(): string
    {
        return 'cartxis.remote_themes.'.sha1($this->baseUrl());
    }
}
