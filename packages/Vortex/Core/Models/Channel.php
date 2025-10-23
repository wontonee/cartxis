<?php

namespace Vortex\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $table = 'channels';

    protected $fillable = [
        'name',
        'slug',
        'theme_id',
        'status',
        'is_default',
        'url',
        'description',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [];

    /**
     * Get the theme associated with the channel
     */
    public function theme()
    {
        return $this->belongsTo(Theme::class, 'theme_id');
    }

    /**
     * Get all active channels
     */
    public static function active()
    {
        return static::where('status', 'active');
    }

    /**
     * Get the default channel
     */
    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Set this channel as default (unsets previous default)
     */
    public function setAsDefault()
    {
        // Unset previous default
        Channel::where('is_default', true)->update(['is_default' => false]);

        // Set this as default
        $this->is_default = true;
        $this->save();

        return $this;
    }

    /**
     * Toggle channel status
     */
    public function toggleStatus()
    {
        $this->status = $this->status === 'active' ? 'inactive' : 'active';
        $this->save();

        return $this;
    }

    /**
     * Get channel with theme details
     */
    public function getWithTheme()
    {
        return $this->load('theme');
    }

    /**
     * Scope: filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: order by display preference
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('is_default', 'desc')
                     ->orderBy('name', 'asc');
    }
}
