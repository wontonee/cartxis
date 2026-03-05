<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Models;

use Illuminate\Database\Eloquent\Model;

class SavedBlock extends Model
{
    protected $table = 'uieditor_saved_blocks';

    public const TYPE_SECTION = 'section';
    public const TYPE_BLOCK   = 'block';

    protected $fillable = [
        'name',
        'description',
        'type',
        'layout_data',
        'thumbnail',
        'created_by',
    ];

    protected $casts = [
        'layout_data' => 'array',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];
}
