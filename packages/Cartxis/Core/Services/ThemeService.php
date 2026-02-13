<?php

namespace Cartxis\Core\Services;

use Cartxis\Core\Models\Theme;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ThemeService
{
    /**
     * Discover and register themes from the themes/ directory.
     *
     * - Creates DB rows for themes found on disk that aren't in the DB yet.
     * - Updates existing rows with the latest metadata from theme.json.
     * - Marks DB rows whose files have been removed (orphans) so the
     *   admin can see they are missing.
     *
     * @return array<int, array{slug: string, config: array, path: string}>
     */
    public function discover(): array
    {
        $themesPath = base_path('themes');
        $discovered = [];

        if (! is_dir($themesPath)) {
            File::makeDirectory($themesPath, 0755, true);
            return $discovered;
        }

        $directories = File::directories($themesPath);
        $slugsOnDisk = [];

        foreach ($directories as $directory) {
            $slug = basename($directory);
            $configPath = $directory . '/theme.json';

            if (! file_exists($configPath)) {
                continue;
            }

            $config = json_decode(file_get_contents($configPath), true);

            if (! $config) {
                continue;
            }

            $slugsOnDisk[] = $slug;

            $discovered[] = [
                'slug'   => $slug,
                'config' => $config,
                'path'   => $directory,
            ];

            // Register or update in database
            Theme::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'        => $config['name'] ?? $slug,
                    'description' => $config['description'] ?? '',
                    'version'     => $config['version'] ?? '1.0.0',
                    'author'      => $config['author'] ?? '',
                    'author_url'  => $config['author_url'] ?? '',
                    'screenshot'  => $config['screenshot'] ?? '',
                    'is_default'  => $config['is_default'] ?? false,
                ]
            );
        }

        return $discovered;
    }

    /**
     * Get the currently active theme.
     */
    public function active(): ?Theme
    {
        return Theme::active();
    }

    /**
     * Activate a theme by slug.
     */
    public function activate(string $slug): bool
    {
        $theme = Theme::where('slug', $slug)->first();

        if (! $theme || ! $theme->exists()) {
            return false;
        }

        $result = $theme->activate();

        if ($result) {
            Cache::forget('active_theme');

            // Fire theme activated hook
            if (function_exists('do_action')) {
                do_action('theme.activated', $theme);
            }
        }

        return $result;
    }

    /**
     * Return all registered themes.
     *
     * @return \Illuminate\Support\Collection<int, Theme>
     */
    public function all(): Collection
    {
        return Theme::orderBy('name')->get();
    }

    // -----------------------------------------------------------------
    //  Installation helpers
    // -----------------------------------------------------------------

    /**
     * Install theme from a zip file.
     *
     * Extracts to themes/, discovers, and returns the slug of the new theme
     * or null on failure.
     */
    public function install(string $zipPath): ?string
    {
        $themesPath = base_path('themes');

        $zip = new \ZipArchive();

        if ($zip->open($zipPath) !== true) {
            return null;
        }

        // Detect zip structure at top-level
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

            if (count($parts) === 1 && !str_ends_with($zip->getNameIndex($i), '/')) {
                $hasTopLevelFiles = true;
            }
        }

        $topLevelEntries = array_keys($topLevel);
        $singleRootDir = (! $hasTopLevelFiles && count($topLevelEntries) === 1) ? $topLevelEntries[0] : null;

        // If zip contains loose files or multiple roots, force extraction into themes/<zip-name>
        $zipBaseName = pathinfo($zipPath, PATHINFO_FILENAME);
        $fallbackFolder = Str::slug($zipBaseName) ?: ('theme-' . time());
        $extractPath = $singleRootDir ? $themesPath : $themesPath . DIRECTORY_SEPARATOR . $fallbackFolder;

        if (! is_dir($extractPath)) {
            File::makeDirectory($extractPath, 0755, true);
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Discover themes to register the new one
        $discovered = $this->discover();

        // Return the slug of the newly installed theme
        if ($singleRootDir && file_exists("{$themesPath}/{$singleRootDir}/theme.json")) {
            return $singleRootDir;
        }

        if (file_exists("{$themesPath}/{$fallbackFolder}/theme.json")) {
            return $fallbackFolder;
        }

        // Fallback: if we can uniquely infer one of extracted top-level folders with theme.json
        foreach ($topLevelEntries as $entry) {
            if (file_exists("{$themesPath}/{$entry}/theme.json")) {
                return $entry;
            }
        }

        // Last fallback: use discovered list only if at least one valid theme exists
        if (! empty($discovered)) {
            $last = end($discovered);
            return $last['slug'] ?? null;
        }

        return null;
    }

    /**
     * Delete a theme by slug.
     */
    public function delete(string $slug): bool
    {
        $theme = Theme::where('slug', $slug)->first();

        if (! $theme) {
            return false;
        }

        // Cannot delete active theme
        if ($theme->is_active) {
            return false;
        }

        // Cannot delete default theme
        if ($theme->is_default) {
            return false;
        }

        // Delete theme directory
        $themePath = $theme->getPath();

        if (is_dir($themePath)) {
            File::deleteDirectory($themePath);
        }

        // Delete from database
        $theme->delete();

        return true;
    }

    /**
     * Get theme settings schema from its config/settings.php file.
     */
    public function getSettingsSchema(Theme $theme): array
    {
        $settingsPath = $theme->getPath() . '/config/settings.php';

        if (file_exists($settingsPath)) {
            return require $settingsPath;
        }

        return [];
    }

    /**
     * Load theme assets — register Blade view namespace + require hooks.php
     */
    public function loadAssets(?Theme $theme = null): void
    {
        $theme = $theme ?? $this->active();

        if (! $theme) {
            return;
        }

        // Add theme views to view paths
        $viewPath = $theme->getPath() . '/resources/views';

        if (is_dir($viewPath)) {
            app('view')->addNamespace("theme.{$theme->slug}", $viewPath);
        }

        // Load theme hooks
        $hooksPath = $theme->getPath() . '/hooks.php';

        if (file_exists($hooksPath)) {
            require_once $hooksPath;
        }
    }
}
