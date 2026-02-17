<?php

namespace Cartxis\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Cartxis\Shop\Models\Order;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'shipment_number',
        'status',
        'carrier',
        'tracking_number',
        'tracking_url',
        'shiprocket_order_id',
        'shiprocket_shipment_id',
        'shiprocket_awb_code',
        'shiprocket_courier_name',
        'shiprocket_status',
        'shiprocket_tracking_payload',
        'shiprocket_synced_at',
        'shipped_at',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'shiprocket_tracking_payload' => 'array',
        'shiprocket_synced_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shipment) {
            if (empty($shipment->shipment_number)) {
                $shipment->shipment_number = static::generateShipmentNumber();
            }
        });
    }

    /**
     * Generate a unique shipment number.
     */
    public static function generateShipmentNumber(): string
    {
        $date = now()->format('Ymd');
        $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        
        $shipmentNumber = "SHIP-{$date}-{$random}";
        
        // Ensure uniqueness
        while (static::where('shipment_number', $shipmentNumber)->exists()) {
            $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $shipmentNumber = "SHIP-{$date}-{$random}";
        }
        
        return $shipmentNumber;
    }

    /**
     * Get the order that owns the shipment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the shipment items for the shipment.
     */
    public function shipmentItems(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    /**
     * Get the order items through shipment items.
     */
    public function items()
    {
        return $this->hasManyThrough(
            \Cartxis\Shop\Models\OrderItem::class,
            ShipmentItem::class,
            'shipment_id',
            'id',
            'id',
            'order_item_id'
        );
    }

    /**
     * Scope: Filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get shipped shipments
     */
    public function scopeShipped($query)
    {
        return $query->whereNotNull('shipped_at');
    }

    /**
     * Scope: Get pending shipments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('shipped_at', [$startDate, $endDate]);
    }

    /**
     * Check if shipment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if shipment is shipped
     */
    public function isShipped(): bool
    {
        return $this->status === 'shipped';
    }

    /**
     * Check if shipment is in transit
     */
    public function isInTransit(): bool
    {
        return $this->status === 'in_transit';
    }

    /**
     * Check if shipment is delivered
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if shipment is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if shipment can be edited
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['pending', 'shipped']);
    }

    /**
     * Check if shipment can be cancelled
     */
    public function canCancel(): bool
    {
        return !in_array($this->status, ['delivered', 'cancelled']);
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'pending' => ['label' => 'Pending', 'class' => 'bg-yellow-100 text-yellow-800 border border-yellow-200'],
            'shipped' => ['label' => 'Shipped', 'class' => 'bg-blue-100 text-blue-800 border border-blue-200'],
            'in_transit' => ['label' => 'In Transit', 'class' => 'bg-indigo-100 text-indigo-800 border border-indigo-200'],
            'out_for_delivery' => ['label' => 'Out for Delivery', 'class' => 'bg-purple-100 text-purple-800 border border-purple-200'],
            'delivered' => ['label' => 'Delivered', 'class' => 'bg-green-100 text-green-800 border border-green-200'],
            'failed' => ['label' => 'Failed', 'class' => 'bg-red-100 text-red-800 border border-red-200'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-gray-100 text-gray-800 border border-gray-200'],
            default => ['label' => 'Unknown', 'class' => 'bg-gray-100 text-gray-800 border border-gray-200'],
        };
    }

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SHIPPED => 'Shipped',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_OUT_FOR_DELIVERY => 'Out for Delivery',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }
}
