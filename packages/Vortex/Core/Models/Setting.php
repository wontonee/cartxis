<?php

namespace Vortex\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'is_public',
        'extension_code',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the value cast to the appropriate type.
     */
    public function getValueAttribute($value)
    {
        return match ($this->type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            'array' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Set the value cast from the appropriate type.
     */
    public function setValueAttribute($value): void
    {
        // Get type from attributes if it exists, otherwise treat as string
        $type = $this->attributes['type'] ?? 'string';
        
        $this->attributes['value'] = match ($type) {
            'boolean' => (int) $value,
            'json', 'array' => is_string($value) ? $value : json_encode($value),
            default => $value,
        };
    }

    /**
     * Scope to get settings by group.
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to get only public settings.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}
