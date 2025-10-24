<?php

namespace Vortex\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxClass extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($class) {
            if ($class->is_default) {
                static::where('id', '!=', $class->id)->update(['is_default' => false]);
            }
        });
    }

    public function rules(): HasMany
    {
        return $this->hasMany(TaxRule::class);
    }
}
