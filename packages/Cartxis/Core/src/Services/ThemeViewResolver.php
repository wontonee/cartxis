<?php

namespace Cartxis\Core\Services;

use Illuminate\Support\Facades\File;

class ThemeViewResolver
{
    protected ?string $activeTheme = null;

    protected bool $resolved = false;

    protected string $defaultTheme = 'cartxis-default';

    public function __construct(
        protected ThemePathResolver $paths,
    ) {
    }

    protected function getActiveThemeSlug(): string
    {
        if (! $this->resolved) {
            $this->activeTheme = config('theme.active', $this->defaultTheme);
            $this->resolved = true;
        }

        return $this->activeTheme ?? $this->defaultTheme;
    }

    public function resolve(string $view): string
    {
        $active = $this->getActiveThemeSlug();

        if ($this->hasThemeView($active, $view)) {
            return $this->formatThemePath($active, $view);
        }

        if ($active !== $this->defaultTheme && $this->hasThemeView($this->defaultTheme, $view)) {
            return $this->formatThemePath($this->defaultTheme, $view);
        }

        return "Frontend/{$view}";
    }

    protected function hasThemeView(string $theme, string $view): bool
    {
        return File::exists($this->getThemeViewPath($theme, $view));
    }

    protected function getThemeViewPath(string $theme, string $view): string
    {
        return $this->paths->pageViewPath($theme, $view);
    }

    protected function formatThemePath(string $theme, string $view): string
    {
        return $this->paths->inertiaComponentPath($theme, $view);
    }

    public function inertia(string $view, array $props = [])
    {
        return \Inertia\Inertia::render($this->resolve($view), $props);
    }

    public function getActiveTheme(): string
    {
        return $this->getActiveThemeSlug();
    }

    public function setActiveTheme(string $theme): self
    {
        $this->activeTheme = $theme;
        $this->resolved = true;

        return $this;
    }

    public function getAvailableThemes(): array
    {
        $storefrontRoot = $this->paths->storefrontRoot();

        if (! File::exists($storefrontRoot)) {
            return [$this->defaultTheme];
        }

        $themes = [];

        foreach (File::directories($storefrontRoot) as $categoryPath) {
            foreach (File::directories($categoryPath) as $directory) {
                $themeName = basename($directory);

                if ($this->isValidTheme($themeName)) {
                    $themes[] = $themeName;
                }
            }
        }

        return $themes;
    }

    protected function isValidTheme(string $theme): bool
    {
        return $this->paths->isInstalled($theme);
    }
}
