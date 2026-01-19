<?php

declare(strict_types=1);

namespace Cartxis\Customer\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'note',
        'is_visible_to_customer',
    ];

    protected $casts = [
        'is_visible_to_customer' => 'boolean',
    ];

    /**
     * Get the customer this note belongs to.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the admin user who created this note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope query to visible notes.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible_to_customer', true);
    }

    /**
     * Scope query to internal notes.
     */
    public function scopeInternal($query)
    {
        return $query->where('is_visible_to_customer', false);
    }
}
