<?php

namespace Cartxis\Core\Services;

use Cartxis\Core\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Cache key prefix.
     */
    protected string $cachePrefix = 'settings:';

    /**
     * Cache duration in seconds (1 hour).
     */
    protected int $cacheDuration = 3600;

    /**
     * Get a setting value.
     */
    public function get(string $key, $default = null)
    {
        $cacheKey = $this->cachePrefix . $key;

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value.
     */
    public function set(string $key, $value, string $type = 'string', ?string $group = null): Setting
    {
        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ]
        );

        // Clear cache
        Cache::forget($this->cachePrefix . $key);

        return $setting;
    }

    /**
     * Check if a setting exists.
     */
    public function has(string $key): bool
    {
        return Setting::where('key', $key)->exists();
    }

    /**
     * Delete a setting.
     */
    public function delete(string $key): bool
    {
        Cache::forget($this->cachePrefix . $key);
        return Setting::where('key', $key)->delete() > 0;
    }

    /**
     * Get all settings in a group.
     */
    public function getGroup(string $group): Collection
    {
        return Setting::byGroup($group)->get()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        });
    }

    /**
     * Get all public settings.
     */
    public function getPublic(): Collection
    {
        return Setting::public()->get()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        });
    }

    /**
     * Set multiple settings at once.
     */
    public function setMultiple(array $settings, ?string $group = null): void
    {
        foreach ($settings as $key => $value) {
            $type = $this->inferType($value);
            $this->set($key, $value, $type, $group);
        }
    }

    /**
     * Infer the type of a value.
     */
    protected function inferType($value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'array',
            default => 'string',
        };
    }

    /**
     * Clear all settings cache.
     */
    public function clearCache(): void
    {
        $settings = Setting::all();
        
        foreach ($settings as $setting) {
            Cache::forget($this->cachePrefix . $setting->key);
        }
    }

    /**
     * Get all settings.
     */
    public function all(): Collection
    {
        return Setting::all()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        });
    }

    /**
     * Get settings by extension.
     */
    public function getByExtension(string $extensionCode): Collection
    {
        return Setting::where('extension_code', $extensionCode)->get()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        });
    }

    /**
     * Delete settings by extension.
     */
    public function deleteByExtension(string $extensionCode): bool
    {
        $settings = Setting::where('extension_code', $extensionCode)->get();
        
        foreach ($settings as $setting) {
            Cache::forget($this->cachePrefix . $setting->key);
        }

        return Setting::where('extension_code', $extensionCode)->delete() > 0;
    }
}
