<?php

declare(strict_types=1);

namespace Cartxis\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, mixed $value): void
    {
        [$group, $settingKey] = static::parseKey($key);

        static::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'key' => $key,
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => static::inferType($value),
            ]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Set multiple settings at once
     */
    public static function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            static::set($key, $value);
        }
    }

    /**
     * Parse the setting key into group and key
     */
    protected static function parseKey(string $key): array
    {
        $parts = explode('.', $key, 2);
        
        return [
            $parts[0] ?? 'general',
            $key,
        ];
    }

    /**
     * Infer the type from the value
     */
    protected static function inferType(mixed $value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_int($value)) {
            return 'integer';
        }

        if (is_float($value)) {
            return 'float';
        }

        if (is_array($value)) {
            return 'json';
        }

        return 'string';
    }

    /**
     * Cast the value to the appropriate type
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Clear setting cache
     */
    public static function clearCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget("setting.{$key}");
        } else {
            Cache::tags(['settings'])->flush();
        }
    }
}
