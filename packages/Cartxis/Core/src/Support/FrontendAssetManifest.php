<?php

declare(strict_types=1);

namespace Cartxis\Core\Support;

/**
 * Detects whether production Vite assets are present (shared-hosting zip or npm build).
 */
final class FrontendAssetManifest
{
    public static function path(?string $publicPath = null): string
    {
        $root = $publicPath ?? public_path();

        return rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'manifest.json';
    }

    public static function exists(?string $publicPath = null): bool
    {
        return is_file(self::path($publicPath));
    }
}
