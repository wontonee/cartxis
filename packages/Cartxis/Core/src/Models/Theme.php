<?php

namespace Cartxis\Core\Models;

use Cartxis\Core\Services\ThemePathResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'catalog_slug',
        'description',
        'version',
        'author',
        'author_url',
        'screenshot',
        'is_active',
        'is_default',
        'source',
        'category',
        'installed_from_catalog_at',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'settings' => 'array',
        'installed_from_catalog_at' => 'datetime',
    ];

    /**
     * Get theme settings
     */
    public function themeSettings(): HasMany
    {
        return $this->hasMany(ThemeSetting::class);
    }

    /**
     * Get the active theme
     */
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Activate this theme
     */
    public function activate(): bool
    {
        // Deactivate all themes first
        static::query()->update(['is_active' => false]);

        // Mass update bypasses model state — sync so re-activating this theme still persists.
        $this->is_active = false;

        // Activate this theme
        $activated = $this->update(['is_active' => true]);

        if ($activated) {
            Cache::forget('active_theme');
        }

        return $activated;
    }

    /**
     * Get theme path under templates/storefront/{category}/{slug}
     */
    public function getPath(): string
    {
        return app(ThemePathResolver::class)->resolveOrFail($this->slug, $this->category);
    }

    /**
     * Check if theme package exists on disk
     */
    public function exists(): bool
    {
        return app(ThemePathResolver::class)->isInstalled($this->slug, $this->category);
    }

    /**
     * Get theme config
     */
    public function getConfig(): array
    {
        $configPath = $this->getPath() . '/theme.json';
        
        if (file_exists($configPath)) {
            return json_decode(file_get_contents($configPath), true) ?? [];
        }

        return [];
    }

    /**
     * Get theme setting value
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Set theme setting value
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->update(['settings' => $settings]);
    }

    /**
     * Get theme asset URL
     */
    public function asset(string $path): string
    {
        return app(ThemePathResolver::class)->publicAssetUrl($this->slug, $path);
    }

    /**
     * Get theme view path
     */
    public function viewPath(string $view): string
    {
        return "themes.{$this->slug}.resources.views.{$view}";
    }
}
