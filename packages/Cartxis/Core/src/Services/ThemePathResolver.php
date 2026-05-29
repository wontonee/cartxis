<?php

declare(strict_types=1);

namespace Cartxis\Core\Services;

use Cartxis\Core\Models\Theme;
use Illuminate\Support\Facades\File;

class ThemePathResolver
{
    public function catalogRoot(): string
    {
        return (string) config('theme.catalog_path', base_path('templates'));
    }

    public function storefrontRoot(): string
    {
        return $this->catalogRoot() . DIRECTORY_SEPARATOR . 'storefront';
    }

    /**
     * Absolute path to a storefront template package, e.g.
     * templates/storefront/general/cartxis-default
     */
    public function resolve(string $slug, ?string $category = null): ?string
    {
        if ($category !== null && $category !== '') {
            $direct = $this->storefrontRoot() . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $slug;

            if ($this->isValidPackage($direct)) {
                return $direct;
            }
        }

        $theme = Theme::query()->where('slug', $slug)->value('category');

        if (is_string($theme) && $theme !== '') {
            $fromDb = $this->storefrontRoot() . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . $slug;

            if ($this->isValidPackage($fromDb)) {
                return $fromDb;
            }
        }

        if (! is_dir($this->storefrontRoot())) {
            return null;
        }

        foreach (File::directories($this->storefrontRoot()) as $categoryPath) {
            $candidate = $categoryPath . DIRECTORY_SEPARATOR . $slug;

            if ($this->isValidPackage($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function resolveOrFail(string $slug, ?string $category = null): string
    {
        $path = $this->resolve($slug, $category);

        if ($path === null) {
            throw new \RuntimeException("Storefront template '{$slug}' was not found under templates/storefront/.");
        }

        return $path;
    }

    public function categoryForSlug(string $slug): ?string
    {
        if (! is_dir($this->storefrontRoot())) {
            return null;
        }

        foreach (File::directories($this->storefrontRoot()) as $categoryPath) {
            $candidate = $categoryPath . DIRECTORY_SEPARATOR . $slug;

            if ($this->isValidPackage($candidate)) {
                return basename($categoryPath);
            }
        }

        return null;
    }

    public function installPath(string $category, string $slug): string
    {
        return $this->storefrontRoot() . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $slug;
    }

    public function isInstalled(string $slug, ?string $category = null): bool
    {
        return $this->resolve($slug, $category) !== null;
    }

    public function publicPath(string $slug): string
    {
        return public_path('templates/' . $slug);
    }

    public function publicAssetUrl(string $slug, string $path = ''): string
    {
        $suffix = $path !== '' ? '/' . ltrim($path, '/') : '';

        return asset('templates/' . $slug . $suffix);
    }

    public function inertiaComponentPath(string $slug, string $view): string
    {
        return "templates/{$slug}/pages/{$view}";
    }

    public function pageViewPath(string $slug, string $view, ?string $category = null): string
    {
        return $this->resolveOrFail($slug, $category)
            . '/resources/views/pages/'
            . $view
            . '.vue';
    }

    public function isValidPackage(string $path): bool
    {
        return is_dir($path)
            && file_exists($path . '/theme.json')
            && is_dir($path . '/resources/views/pages')
            && is_dir($path . '/resources/views/components')
            && is_dir($path . '/resources/views/layouts');
    }
}
