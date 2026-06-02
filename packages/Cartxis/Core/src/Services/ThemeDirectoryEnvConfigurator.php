<?php

declare(strict_types=1);

namespace Cartxis\Core\Services;

class ThemeDirectoryEnvConfigurator
{
    public function __construct(
        protected ThemeDirectoryRegistrationService $registration,
        protected ?string $envPath = null,
    ) {
        $this->envPath = $envPath ?? base_path('.env');
    }

    /**
     * @return array{status: string, message: string, api_key: string|null}
     */
    public function ensureConfigured(string $appName, string $appUrl): array
    {
        if ($this->isApiKeyConfigured()) {
            return [
                'status' => 'already_configured',
                'message' => 'Theme directory API key already configured.',
                'api_key' => $this->readApiKeyFromEnvFile() ?? (string) config('theme.directory.api_key'),
            ];
        }

        $this->writeDirectoryUrl();

        $registration = $this->registration->registerWithDiagnostics($appName, $appUrl);
        $apiKey = $registration['key'];

        if ($apiKey === null || $apiKey === '') {
            $details = $registration['error'] ?? 'Unknown error.';
            $status = $registration['status'];
            $statusLabel = $status !== null ? "HTTP {$status}" : 'connection failed';

            return [
                'status' => 'failed',
                'message' => "Could not register theme directory key ({$statusLabel}).",
                'api_key' => null,
                'directory_url' => $registration['url'],
                'http_status' => $status,
                'error' => $details,
            ];
        }

        $this->writeEnvValue('CARTXIS_THEME_API_KEY', $apiKey);
        putenv('CARTXIS_THEME_API_KEY='.$apiKey);
        config(['theme.directory.api_key' => $apiKey]);

        return [
            'status' => 'registered',
            'message' => 'Theme directory API key generated and saved to .env.',
            'api_key' => $apiKey,
        ];
    }

    public function isApiKeyConfigured(): bool
    {
        if (filled($this->readApiKeyFromEnvFile())) {
            return true;
        }

        return filled(config('theme.directory.api_key'));
    }

    public function readApiKeyFromEnvFile(): ?string
    {
        if (! file_exists($this->envPath)) {
            return null;
        }

        $content = (string) file_get_contents($this->envPath);

        if (! preg_match('/^CARTXIS_THEME_API_KEY=(.*)$/m', $content, $matches)) {
            return null;
        }

        $value = trim($matches[1]);

        if ($value === '' || $value === '""' || $value === "''") {
            return null;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        return $value !== '' ? $value : null;
    }

    protected function writeDirectoryUrl(): void
    {
        $directoryUrl = \Cartxis\Core\Support\ThemeDirectoryUrl::resolve();

        $this->writeEnvValue('CARTXIS_THEME_DIRECTORY_URL', $directoryUrl);
        config(['theme.directory.url' => $directoryUrl]);
    }

    protected function writeEnvValue(string $key, string $value): void
    {
        $content = file_exists($this->envPath) ? (string) file_get_contents($this->envPath) : '';

        $safeValue = str_contains($value, ' ') ? '"'.addslashes($value).'"' : $value;
        $line = "{$key}={$safeValue}";

        if (preg_match("/^{$key}=.*/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", $line, $content);
        } else {
            $content .= PHP_EOL.$line;
        }

        file_put_contents($this->envPath, $content);
    }
}
