<?php

namespace Vortex\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'extensions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'version',
        'author',
        'author_url',
        'icon',
        'requires',
        'config',
        'active',
        'installed',
        'installed_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'active' => 'boolean',
        'installed' => 'boolean',
        'requires' => 'array',
        'config' => 'array',
        'installed_at' => 'datetime',
    ];

    /**
     * Scope to get only active extensions.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get only installed extensions.
     */
    public function scopeInstalled($query)
    {
        return $query->where('installed', true);
    }

    /**
     * Check if extension can be activated.
     */
    public function canActivate(): bool
    {
        return $this->installed && !$this->active;
    }

    /**
     * Check if extension can be deactivated.
     */
    public function canDeactivate(): bool
    {
        return $this->installed && $this->active;
    }

    /**
     * Check if extension can be uninstalled.
     */
    public function canUninstall(): bool
    {
        return $this->installed;
    }

    /**
     * Get extension configuration value.
     */
    public function getConfig(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Set extension configuration value.
     */
    public function setConfig(string $key, $value): void
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->config = $config;
        $this->save();
    }
}
