<?php

namespace Cartxis\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxZoneLocation extends Model
{
    protected $fillable = [
        'tax_zone_id',
        'country_code',
        'state_code',
        'postal_code_pattern',
        'city',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(TaxZone::class, 'tax_zone_id');
    }
}
