<?php

namespace Cartxis\Core\Services;

use Cartxis\Core\Models\Theme;
use Cartxis\Shop\Services\HomeService;
use Cartxis\UIEditor\Models\PageLayout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ThemeService
{
    public function __construct(
        protected ThemePathResolver $paths,
    ) {}

    /**
     * Discover and register storefront templates from templates/storefront/{category}/{slug}/.
     *
     * @return array<int, array{slug: string, config: array, path: string, category: string}>
     */
    public function discover(): array
    {
        $discovered = [];
        $storefrontRoot = $this->paths->storefrontRoot();

        if (! is_dir($storefrontRoot)) {
            File::ensureDirectoryExists($storefrontRoot);

            return $discovered;
        }

        $slugsOnDisk = [];

        foreach (File::directories($storefrontRoot) as $categoryPath) {
            $category = basename($categoryPath);

            foreach (File::directories($categoryPath) as $directory) {
                $this->flattenNestedPackage($directory);

                $slug = basename($directory);
                $configPath = $directory.'/theme.json';

                if (! file_exists($configPath)) {
                    continue;
                }

                $config = json_decode((string) file_get_contents($configPath), true);

                if (! $config) {
                    continue;
                }

                $slugsOnDisk[] = $slug;

                $templateJsonPath = $directory.'/template.json';
                $isCatalogPackage = file_exists($templateJsonPath);
                $isBundledDefault = (bool) ($config['is_default'] ?? false);
                $existingTheme = Theme::where('slug', $slug)->first();

                // Optional Template Zone packages (template.json present) are browse-only
                // until explicitly installed — do not register them in the themes table.
                if ($isCatalogPackage && ! $isBundledDefault && $existingTheme === null) {
                    continue;
                }

                $discovered[] = [
                    'slug' => $slug,
                    'category' => $category,
                    'config' => $config,
                    'path' => $directory,
                ];

                Theme::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $config['name'] ?? $slug,
                        'description' => $config['description'] ?? '',
                        'version' => $config['version'] ?? '1.0.0',
                        'author' => $config['author'] ?? '',
                        'author_url' => $config['author_url'] ?? '',
                        'screenshot' => $config['screenshot'] ?? '',
                        'is_default' => $config['is_default'] ?? false,
                        'category' => $category,
                        'source' => $category === 'general' && ($config['is_default'] ?? false)
                            ? 'bundled'
                            : (Theme::where('slug', $slug)->value('source') ?? 'catalog'),
                    ]
                );

                $this->publishPublicAssets($directory, $slug);
            }
        }

        Theme::query()
            ->where('is_default', false)
            ->whereNotIn('slug', $slugsOnDisk)
            ->delete();

        // Remove catalog browse packages that were previously auto-registered.
        Theme::query()
            ->where('is_default', false)
            ->whereNull('installed_from_catalog_at')
            ->where('source', 'catalog')
            ->get()
            ->each(function (Theme $theme) use ($storefrontRoot) {
                $packagePath = $storefrontRoot
                    .DIRECTORY_SEPARATOR.$theme->category
                    .DIRECTORY_SEPARATOR.$theme->slug;

                if (file_exists($packagePath.'/template.json')) {
                    $theme->delete();
                }
            });

        return $discovered;
    }

    /**
     * Copy template assets into public/templates/{slug}/.
     */
    protected function publishPublicAssets(string $themePath, string $slug): void
    {
        $publicThemePath = $this->paths->publicPath($slug);
        File::ensureDirectoryExists($publicThemePath);

        $assetsPath = $themePath.'/assets';

        if (is_dir($assetsPath)) {
            File::copyDirectory($assetsPath, $publicThemePath.'/assets');
        }

        $themeCssPath = $themePath.'/resources/css/theme.css';

        if (file_exists($themeCssPath)) {
            File::ensureDirectoryExists($publicThemePath.'/css');
            File::copy($themeCssPath, $publicThemePath.'/css/theme.css');
        }
    }

    public function active(): ?Theme
    {
        return Theme::active();
    }

    public function activate(string $slug): bool
    {
        $theme = Theme::where('slug', $slug)->first();

        if (! $theme || ! $theme->exists()) {
            return false;
        }

        $result = $theme->activate();

        if ($result) {
            Cache::forget('active_theme');
            $this->restoreBundledTheme($theme->fresh());

            if (function_exists('do_action')) {
                do_action('theme.activated', $theme);
            }
        }

        return $result;
    }

    public function restoreBundledTheme(?Theme $theme = null): void
    {
        $theme = $theme ?? Theme::where('slug', config('theme.default', 'cartxis-default'))->first();

        if (! $theme || ! $theme->exists()) {
            return;
        }

        $config = $theme->getConfig();
        $dataPath = $theme->getPath().'/data/theme-data.json';
        $themeData = file_exists($dataPath)
            ? json_decode((string) file_get_contents($dataPath), true)
            : [];

        if (! empty($config['settings'])) {
            $theme->update(['settings' => $config['settings']]);
        }

        if (! empty($config['native_homepage'])) {
            PageLayout::homepage()->delete();
        } elseif (! empty($themeData['homepage']['sections'])) {
            PageLayout::homepage()->delete();

            PageLayout::create([
                'page_type' => PageLayout::TYPE_HOMEPAGE,
                'page_id' => null,
                'layout_data' => $themeData['homepage'],
                'status' => PageLayout::STATUS_PUBLISHED,
                'published_at' => now(),
            ]);
        }

        if (file_exists($dataPath)) {
            app(ThemeDataImportService::class)->import(
                $theme->slug,
                blocks: true,
                menus: true,
                settings: true,
            );
        }

        Cache::forget('active_theme');

        if (class_exists(HomeService::class)) {
            app(HomeService::class)->clearCache();
        }
    }

    public function all(): Collection
    {
        return Theme::orderBy('name')->get();
    }

    /**
     * Install a storefront template zip into templates/storefront/{category}/{slug}/.
     */
    public function install(string $zipPath, string $category = 'general'): ?string
    {
        $storefrontRoot = $this->paths->storefrontRoot();
        File::ensureDirectoryExists($storefrontRoot.DIRECTORY_SEPARATOR.$category);

        $zip = new \ZipArchive;

        if ($zip->open($zipPath) !== true) {
            return null;
        }

        $topLevel = [];
        $hasTopLevelFiles = false;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);

            if ($entry === false || $entry === '' || str_starts_with($entry, '__MACOSX/')) {
                continue;
            }

            $entry = trim($entry, '/');

            if ($entry === '') {
                continue;
            }

            $parts = explode('/', $entry);
            $first = $parts[0];
            $topLevel[$first] = true;

            if (count($parts) === 1 && ! str_ends_with((string) $zip->getNameIndex($i), '/')) {
                $hasTopLevelFiles = true;
            }
        }

        $topLevelEntries = array_keys($topLevel);
        $singleRootDir = (! $hasTopLevelFiles && count($topLevelEntries) === 1) ? $topLevelEntries[0] : null;
        $zipBaseName = pathinfo($zipPath, PATHINFO_FILENAME);
        $fallbackFolder = Str::slug($zipBaseName) ?: ('template-'.time());
        $categoryPath = $storefrontRoot.DIRECTORY_SEPARATOR.$category;

        if ($singleRootDir !== null) {
            // Zip is packaged as {slug}/theme.json — extract into the category folder.
            $slugFolder = $singleRootDir;
            $extractPath = $categoryPath.DIRECTORY_SEPARATOR.$slugFolder;

            if (is_dir($extractPath)) {
                File::deleteDirectory($extractPath);
            }

            File::ensureDirectoryExists($categoryPath);
            $zip->extractTo($categoryPath);
        } else {
            $slugFolder = $fallbackFolder;
            $extractPath = $this->paths->installPath($category, $slugFolder);

            if (is_dir($extractPath)) {
                File::deleteDirectory($extractPath);
            }

            File::ensureDirectoryExists($extractPath);
            $zip->extractTo($extractPath);
        }

        $zip->close();

        $this->flattenNestedPackage($extractPath);

        $discovered = $this->discover();

        if ($this->paths->isInstalled($slugFolder, $category)) {
            return $slugFolder;
        }

        return null;
    }

    /**
     * Fix zip installs that landed one folder too deep, e.g.
     * templates/storefront/electronics/dmart-electronics/dmart-electronics/theme.json
     */
    public function flattenNestedPackage(string $packagePath): void
    {
        if ($this->paths->isValidPackage($packagePath)) {
            return;
        }

        $childDirs = array_values(array_filter(
            File::directories($packagePath),
            fn (string $dir) => ! in_array(basename($dir), ['__MACOSX', '.git'], true)
        ));

        if (count($childDirs) !== 1) {
            return;
        }

        $nested = $childDirs[0];

        if (! $this->paths->isValidPackage($nested)) {
            return;
        }

        $temp = $packagePath.'_flatten_'.uniqid('', true);
        File::moveDirectory($nested, $temp);
        File::deleteDirectory($packagePath);
        File::moveDirectory($temp, $packagePath);
    }

    public function delete(string $slug): bool
    {
        $theme = Theme::where('slug', $slug)->first();

        if (! $theme || $theme->is_active || $theme->is_default) {
            return false;
        }

        $themePath = $this->paths->resolve($slug, $theme->category);

        if ($themePath && is_dir($themePath)) {
            File::deleteDirectory($themePath);
        }

        $publicThemePath = $this->paths->publicPath($slug);

        if (is_dir($publicThemePath)) {
            File::deleteDirectory($publicThemePath);
        }

        $theme->delete();

        return true;
    }

    public function getSettingsSchema(Theme $theme): array
    {
        $settingsPath = $theme->getPath().'/config/settings.php';

        if (file_exists($settingsPath)) {
            return require $settingsPath;
        }

        return [];
    }

    public function loadAssets(?Theme $theme = null): void
    {
        $theme = $theme ?? $this->active();

        if (! $theme) {
            return;
        }

        $viewPath = $theme->getPath().'/resources/views';

        if (is_dir($viewPath)) {
            app('view')->addNamespace("theme.{$theme->slug}", $viewPath);
        }

        $hooksPath = $theme->getPath().'/hooks.php';

        if (file_exists($hooksPath)) {
            require_once $hooksPath;
        }
    }
}
