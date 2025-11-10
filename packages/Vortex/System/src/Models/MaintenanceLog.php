<?php

declare(strict_types=1);

namespace Vortex\System\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'maintenance_logs';
    
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'action',
        'reason',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'admin_id',
        'admin_name',
        'ip_address',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'created_at' => 'datetime',
    ];
}
