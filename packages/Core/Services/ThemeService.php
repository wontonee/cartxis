<?php

namespace Packages\Core\Services;

use Packages\Core\Models\Theme;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    /**
     * Discover and register themes from themes directory
     */
    public function discover(): array
    {
        $themesPath = base_path('themes');
        $discovered = [];

        if (!is_dir($themesPath)) {
            File::makeDirectory($themesPath, 0755, true);
        }

        $directories = File::directories($themesPath);

        foreach ($directories as $directory) {
            $slug = basename($directory);
            $configPath = $directory . '/theme.json';

            if (file_exists($configPath)) {
                $config = json_decode(file_get_contents($configPath), true);

                if ($config) {
                    $discovered[] = [
                        'slug' => $slug,
                        'config' => $config,
                        'path' => $directory,
                    ];

                    // Register in database if not exists
                    Theme::firstOrCreate(
                        ['slug' => $slug],
                        [
                            'name' => $config['name'] ?? $slug,
                            'description' => $config['description'] ?? '',
                            'version' => $config['version'] ?? '1.0.0',
                            'author' => $config['author'] ?? '',
                            'author_url' => $config['author_url'] ?? '',
                            'screenshot' => $config['screenshot'] ?? '',
                            'is_default' => $config['is_default'] ?? false,
                        ]
                    );
                }
            }
        }

        return $discovered;
    }

    /**
     * Get active theme
     */
    public function active(): ?Theme
    {
        return Cache::remember('active_theme', 3600, function () {
            return Theme::active();
        });
    }

    /**
     * Activate a theme
     */
    public function activate(string $slug): bool
    {
        $theme = Theme::where('slug', $slug)->first();

        if (!$theme || !$theme->exists()) {
            return false;
        }

        $result = $theme->activate();

        if ($result) {
            Cache::forget('active_theme');
            
            // Fire theme activated hook
            do_action('theme.activated', $theme);
        }

        return $result;
    }

    /**
     * Install theme from zip
     */
    public function install(string $zipPath): bool
    {
        $themesPath = base_path('themes');
        
        // Extract zip
        $zip = new \ZipArchive();
        
        if ($zip->open($zipPath) !== true) {
            return false;
        }

        $zip->extractTo($themesPath);
        $zip->close();

        // Discover new themes
        $this->discover();

        return true;
    }

    /**
     * Delete a theme
     */
    public function delete(string $slug): bool
    {
        $theme = Theme::where('slug', $slug)->first();

        if (!$theme) {
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
     * Get theme settings schema
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
     * Load theme assets
     */
    public function loadAssets(?Theme $theme = null): void
    {
        $theme = $theme ?? $this->active();

        if (!$theme) {
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
