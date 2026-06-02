<?php

declare(strict_types=1);

namespace Cartxis\Core\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ThemeAssetBuildService
{
    public function shouldRebuild(): bool
    {
        return (bool) config('theme.rebuild_assets_on_install', true);
    }

    /**
     * Rebuild Vite storefront assets so newly installed theme Vue pages are bundled.
     *
     * Theme pages are discovered via import.meta.glob at build time — installing a theme
     * after the last npm run build leaves the storefront unable to resolve its pages.
     */
    public function rebuild(bool $background = false): bool
    {
        if (! $this->shouldRebuild()) {
            return false;
        }

        if (! is_file(base_path('package.json'))) {
            Log::warning('Theme asset rebuild skipped: package.json not found');

            return false;
        }

        $npm = $this->resolveNpmBinary();

        if ($npm === null) {
            Log::warning('Theme asset rebuild skipped: npm binary not found');

            return false;
        }

        $timeout = (int) config('theme.rebuild_assets_timeout', 300);
        $env = array_filter([
            'PATH' => getenv('PATH') ?: '/usr/local/bin:/usr/bin:/bin:/opt/homebrew/bin',
            'HOME' => getenv('HOME') ?: null,
            'NODE_ENV' => 'production',
        ]);

        $process = new Process([$npm, 'run', 'build'], base_path(), $env, null, $timeout);

        try {
            if ($background) {
                $process->start();

                return true;
            }

            @set_time_limit($timeout + 30);
            $process->run();

            if (! $process->isSuccessful()) {
                Log::warning('Theme asset rebuild failed', [
                    'output' => trim($process->getErrorOutput() ?: $process->getOutput()),
                    'exit_code' => $process->getExitCode(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Theme asset rebuild error', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    protected function resolveNpmBinary(): ?string
    {
        $candidates = ['npm'];

        if ($home = getenv('HOME')) {
            $candidates[] = $home.'/.nvm/versions/node/*/bin/npm';
        }

        foreach ($candidates as $candidate) {
            if (str_contains($candidate, '*')) {
                $matches = glob($candidate) ?: [];

                if ($matches !== []) {
                    return $matches[array_key_last($matches)];
                }

                continue;
            }

            $process = new Process(['which', $candidate]);
            $process->run();

            if ($process->isSuccessful()) {
                return trim($process->getOutput()) ?: null;
            }
        }

        return null;
    }
}
