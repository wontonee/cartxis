<?php

namespace Vortex\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Vortex\Shop\Models\Order;

class OrderHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_histories';
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'order_id',
        'admin_user_id',
        'status_from',
        'status_to',
        'payment_status_from',
        'payment_status_to',
        'comment',
        'customer_notified',
        'visible_to_customer',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'customer_notified' => 'boolean',
        'visible_to_customer' => 'boolean',
        'created_at' => 'datetime',
    ];
    
    /**
     * Get the order that owns the history.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Get the admin user who made the change.
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
    
    /**
     * Get formatted status change description.
     */
    public function getStatusChangeAttribute(): ?string
    {
        if ($this->status_from && $this->status_to) {
            return ucfirst($this->status_from) . ' → ' . ucfirst($this->status_to);
        }
        
        if ($this->payment_status_from && $this->payment_status_to) {
            return 'Payment: ' . ucfirst($this->payment_status_from) . ' → ' . ucfirst($this->payment_status_to);
        }
        
        return null;
    }
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($history) {
            $history->created_at = now();
        });
    }
}
