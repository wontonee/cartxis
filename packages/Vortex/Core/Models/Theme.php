<?php

namespace Vortex\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'author_url',
        'screenshot',
        'is_active',
        'is_default',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'settings' => 'array',
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

        // Activate this theme
        return $this->update(['is_active' => true]);
    }

    /**
     * Get theme path
     */
    public function getPath(): string
    {
        return base_path("themes/{$this->slug}");
    }

    /**
     * Check if theme exists
     */
    public function exists(): bool
    {
        return is_dir($this->getPath());
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
        return asset("themes/{$this->slug}/{$path}");
    }

    /**
     * Get theme view path
     */
    public function viewPath(string $view): string
    {
        return "themes.{$this->slug}.resources.views.{$view}";
    }
}
