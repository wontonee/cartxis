<?php

declare(strict_types=1);

namespace Cartxis\Core\Services;

use Cartxis\Core\Models\Theme;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class TemplateCatalogService
{
    protected string $catalogPath;

    protected string $registryPath;

    public function __construct(
        protected ThemePathResolver $paths,
    ) {
        $this->catalogPath = (string) config('theme.catalog_path', base_path('templates'));
        $this->registryPath = (string) config('theme.catalog_registry', base_path('templates/registry.json'));
    }

    /** @var array<string, mixed>|null */
    protected ?array $registry = null;

    /**
     * @return array<string, mixed>
     */
    public function getRegistry(): array
    {
        if ($this->registry !== null) {
            return $this->registry;
        }

        if (! file_exists($this->registryPath)) {
            return $this->registry = [
                'remote_url' => null,
                'types' => [],
                'categories' => [],
            ];
        }

        $decoded = json_decode((string) file_get_contents($this->registryPath), true);

        return $this->registry = is_array($decoded) ? $decoded : [
            'remote_url' => null,
            'types' => [],
            'categories' => [],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCategories(): array
    {
        $categories = $this->getRegistry()['categories'] ?? [];
        $knownSlugs = collect($categories)->pluck('slug')->filter()->all();

        foreach ($this->discover() as $entry) {
            $slug = (string) ($entry['category'] ?? '');

            if ($slug === '' || in_array($slug, $knownSlugs, true)) {
                continue;
            }

            $categories[] = [
                'slug' => $slug,
                'label' => $this->humanizeCategorySlug($slug),
                'sort' => 99,
            ];
            $knownSlugs[] = $slug;
        }

        usort($categories, fn (array $a, array $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

        return $categories;
    }

    public function getCategoryLabel(string $slug): string
    {
        foreach ($this->getRegistry()['categories'] ?? [] as $category) {
            if (($category['slug'] ?? '') === $slug) {
                return (string) ($category['label'] ?? $slug);
            }
        }

        return $this->humanizeCategorySlug($slug);
    }

    protected function humanizeCategorySlug(string $slug): string
    {
        return collect(preg_split('/[-_]+/', $slug) ?: [])
            ->filter()
            ->map(fn (string $part) => ucfirst(strtolower($part)))
            ->implode(' ');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTypes(): array
    {
        return $this->getRegistry()['types'] ?? [];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function discover(?string $type = null, ?string $category = null): Collection
    {
        $entries = collect();

        if (! is_dir($this->catalogPath)) {
            return $entries;
        }

        $enabledTypes = collect($this->getTypes())
            ->filter(fn (array $item) => ($item['enabled'] ?? false) === true)
            ->pluck('slug')
            ->all();

        if ($type !== null && ! in_array($type, $enabledTypes, true)) {
            return $entries;
        }

        $typesToScan = $type !== null ? [$type] : $enabledTypes;

        foreach ($typesToScan as $typeSlug) {
            $typePath = $this->catalogPath . DIRECTORY_SEPARATOR . $typeSlug;

            if (! is_dir($typePath)) {
                continue;
            }

            foreach (File::directories($typePath) as $categoryPath) {
                $categorySlug = basename($categoryPath);

                if ($category !== null && $categorySlug !== $category) {
                    continue;
                }

                foreach (File::directories($categoryPath) as $templatePath) {
                    $entry = $this->buildEntry($templatePath, $typeSlug, $categorySlug);

                    if ($entry !== null) {
                        $entries->push($entry);
                    }
                }
            }
        }

        return $entries->sortBy(fn (array $item) => [$item['category'] ?? '', $item['name'] ?? '', $item['slug'] ?? ''])->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $slug): ?array
    {
        return $this->discover()->firstWhere('slug', $slug);
    }

    public function isInstalled(string $slug): bool
    {
        return $this->paths->isInstalled($slug);
    }

    public function getInstalledVersion(string $slug): ?string
    {
        $themeJsonPath = $this->paths->resolve($slug);

        if ($themeJsonPath === null) {
            return null;
        }

        $themeJsonPath .= '/theme.json';

        if (! file_exists($themeJsonPath)) {
            return null;
        }

        $config = json_decode((string) file_get_contents($themeJsonPath), true);

        return is_array($config) ? ($config['version'] ?? null) : null;
    }

    public function hasUpdateAvailable(array $entry): bool
    {
        if (! $this->isInstalled($entry['slug'])) {
            return false;
        }

        $catalogVersion = (string) ($entry['version'] ?? '0.0.0');
        $installedVersion = (string) ($this->getInstalledVersion($entry['slug']) ?? '0.0.0');

        return version_compare($catalogVersion, $installedVersion, '>');
    }

    public function resolveScreenshotUrl(array $entry): ?string
    {
        $screenshot = $entry['screenshot'] ?? null;

        if (! $screenshot) {
            return null;
        }

        $slug = (string) ($entry['slug'] ?? '');
        $sourcePath = rtrim((string) ($entry['path'] ?? ''), '/\\') . '/' . ltrim((string) $screenshot, '/');
        $publicRelativePath = "templates/{$slug}/" . ltrim((string) $screenshot, '/');
        $publicPath = public_path($publicRelativePath);

        if (! file_exists($publicPath) && file_exists($sourcePath)) {
            File::ensureDirectoryExists(dirname($publicPath));
            @copy($sourcePath, $publicPath);
        }

        return file_exists($publicPath) ? asset($publicRelativePath) : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function buildEntry(string $templatePath, string $type, string $category): ?array
    {
        $templateJsonPath = $templatePath . DIRECTORY_SEPARATOR . 'template.json';

        if (! file_exists($templateJsonPath)) {
            return null;
        }

        $manifest = json_decode((string) file_get_contents($templateJsonPath), true);

        if (! is_array($manifest)) {
            return null;
        }

        $slug = (string) ($manifest['slug'] ?? basename($templatePath));

        if ($type === 'storefront' && ! file_exists($templatePath . DIRECTORY_SEPARATOR . 'theme.json')) {
            return null;
        }

        $themeConfig = [];
        $themeJsonPath = $templatePath . DIRECTORY_SEPARATOR . 'theme.json';

        if (file_exists($themeJsonPath)) {
            $decoded = json_decode((string) file_get_contents($themeJsonPath), true);
            $themeConfig = is_array($decoded) ? $decoded : [];
        }

        $hasDemoLayout = false;
        $themeDataPath = $templatePath . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'theme-data.json';

        if (file_exists($themeDataPath)) {
            $themeData = json_decode((string) file_get_contents($themeDataPath), true);
            $hasDemoLayout = ! empty($themeData['homepage']);
        }

        $entry = array_merge($manifest, [
            'slug' => $slug,
            'type' => $manifest['type'] ?? $type,
            'category' => $manifest['category'] ?? $category,
            'category_label' => $this->getCategoryLabel((string) ($manifest['category'] ?? $category)),
            'name' => $manifest['name'] ?? ($themeConfig['name'] ?? $slug),
            'description' => $manifest['description'] ?? ($themeConfig['description'] ?? ''),
            'version' => $manifest['version'] ?? ($themeConfig['version'] ?? '1.0.0'),
            'author' => $manifest['author'] ?? ($themeConfig['author'] ?? ''),
            'author_url' => $manifest['author_url'] ?? ($themeConfig['author_url'] ?? null),
            'screenshot' => $manifest['screenshot'] ?? ($themeConfig['screenshot'] ?? null),
            'native_homepage' => (bool) ($manifest['native_homepage'] ?? ($themeConfig['native_homepage'] ?? false)),
            'path' => $templatePath,
            'has_demo_layout' => $hasDemoLayout,
            'installed' => $this->isInstalled($slug),
            'is_active' => Theme::where('slug', $slug)->where('is_active', true)->exists(),
            'installed_version' => $this->getInstalledVersion($slug),
            'update_available' => false,
        ]);

        $entry['update_available'] = $this->hasUpdateAvailable($entry);
        $entry['screenshot_url'] = $this->resolveScreenshotUrl($entry);

        return $entry;
    }

    /**
     * @return array<int, string>
     */
    public function validateCatalog(): array
    {
        $errors = [];

        foreach ($this->discover() as $entry) {
            $slug = (string) ($entry['slug'] ?? 'unknown');
            $path = (string) ($entry['path'] ?? '');

            if (! file_exists($path . DIRECTORY_SEPARATOR . 'template.json')) {
                $errors[] = "{$slug}: missing template.json";
            }

            if (($entry['type'] ?? '') === 'storefront' && ! file_exists($path . DIRECTORY_SEPARATOR . 'theme.json')) {
                $errors[] = "{$slug}: missing theme.json";
            }

            if (
                ($entry['has_demo_layout'] ?? false) === false
                && empty($entry['native_homepage'])
                && file_exists($path . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'theme-data.json')
            ) {
                $themeData = json_decode((string) file_get_contents($path . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'theme-data.json'), true);
                if (empty($themeData['homepage'])) {
                    $errors[] = "{$slug}: theme-data.json missing homepage section";
                }
            }
        }

        return $errors;
    }
}
