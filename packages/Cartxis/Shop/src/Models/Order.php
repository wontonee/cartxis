<?php

namespace Cartxis\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Cartxis\Customer\Models\Customer;
use Cartxis\Shop\Models\OrderItem;
use Cartxis\Shop\Models\Address;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'customer_id',
        'order_number',
        'status',
        'subtotal',
        'tax',
        'shipping_cost',
        'discount',
        'total',
        'payment_method',
        'payment_data',
        'payment_status',
        'shipping_method',
        'tracking_number',
        'notes',
        'customer_email',
        'customer_phone',
        'ip_address',
        'user_agent',
        'source_channel',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Order status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_FAILED = 'failed';

    /**
     * Payment status constants
     */
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_REFUNDED = 'refunded';

    /**
     * Get all available order statuses.
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REFUNDED => 'Refunded',
            self::STATUS_FAILED => 'Failed',
        ];
    }

    /**
     * Get all available payment statuses.
     *
     * @return array
     */
    public static function getPaymentStatuses(): array
    {
        return [
            self::PAYMENT_PENDING => 'Pending',
            self::PAYMENT_PAID => 'Paid',
            self::PAYMENT_FAILED => 'Failed',
            self::PAYMENT_REFUNDED => 'Refunded',
        ];
    }

    /**
     * Get the user that owns the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer that owns the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the order items for the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get all of the order's addresses (polymorphic).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the shipping address.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function shippingAddress(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable')
            ->where('type', Address::TYPE_SHIPPING);
    }

    /**
     * Get billing address for the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function billingAddress(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable')
            ->where('type', Address::TYPE_BILLING);
    }

    /**
     * Get order histories (admin actions/status changes).
     */
    public function histories(): HasMany
    {
        return $this->hasMany(\Cartxis\Sales\Models\OrderHistory::class);
    }

    /**
     * Get order invoices.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(\Cartxis\Sales\Models\Invoice::class);
    }

    /**
     * Get order shipments.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(\Cartxis\Sales\Models\Shipment::class);
    }

    /**
     * Check if order is paid.
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    /**
     * Check if order is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if order is processing.
     *
     * @return bool
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if order is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if order is cancelled.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if order can be cancelled.
     *
     * @return bool
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Check if order can be shipped.
     *
     * @return bool
     */
    public function canBeShipped(): bool
    {
        return $this->isPaid() && 
               !$this->isCancelled() && 
               !$this->isFullyShipped();
    }

    /**
     * Check if order is fully shipped.
     *
     * @return bool
     */
    public function isFullyShipped(): bool
    {
        // Get total quantity ordered
        $orderedQuantity = $this->items->sum('quantity');
        
        // Get total quantity shipped
        $shippedQuantity = 0;
        foreach ($this->shipments as $shipment) {
            if (!in_array($shipment->status, ['cancelled', 'failed'])) {
                $shippedQuantity += $shipment->shipmentItems->sum('quantity');
            }
        }
        
        return $orderedQuantity > 0 && $shippedQuantity >= $orderedQuantity;
    }

    /**
     * Check if order is partially shipped.
     *
     * @return bool
     */
    public function isPartiallyShipped(): bool
    {
        if ($this->shipments->isEmpty()) {
            return false;
        }
        
        // Has at least one valid shipment but not fully shipped
        $hasValidShipment = $this->shipments()
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->exists();
            
        return $hasValidShipment && !$this->isFullyShipped();
    }

    /**
     * Get remaining quantity to ship for an order item.
     *
     * @param int $orderItemId
     * @return int
     */
    public function getRemainingQuantityToShip(int $orderItemId): int
    {
        $orderItem = $this->items->find($orderItemId);
        if (!$orderItem) {
            return 0;
        }
        
        $orderedQuantity = $orderItem->quantity;
        
        // Calculate shipped quantity from all valid shipments
        $shippedQuantity = 0;
        foreach ($this->shipments as $shipment) {
            if (!in_array($shipment->status, ['cancelled', 'failed'])) {
                $shipmentItem = $shipment->shipmentItems
                    ->where('order_item_id', $orderItemId)
                    ->first();
                    
                if ($shipmentItem) {
                    $shippedQuantity += $shipmentItem->quantity;
                }
            }
        }
        
        return max(0, $orderedQuantity - $shippedQuantity);
    }

    /**
     * Generate a unique order number.
     *
     * @return string
     */
    public static function generateOrderNumber(): string
    {
        $prefix = config('shop.order.number_prefix', 'ORD');
        $number = str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        $timestamp = now()->format('ymd');
        
        return $prefix . '-' . $timestamp . '-' . $number;
    }

    /**
     * Scope a query to only include orders with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include paid orders.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }

    /**
     * Scope a query to only include orders for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the order's formatted total.
     *
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        $currency = \Cartxis\Core\Models\Currency::getDefault();
        return $currency ? $currency->format($this->total) : '$' . number_format($this->total, 2);
    }

    /**
     * Get the order's formatted subtotal.
     *
     * @return string
     */
    public function getFormattedSubtotalAttribute(): string
    {
        $currency = \Cartxis\Core\Models\Currency::getDefault();
        return $currency ? $currency->format($this->subtotal) : '$' . number_format($this->subtotal, 2);
    }

    /**
     * Get the order's status label.
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        $statuses = self::getStatuses();
        return $statuses[$this->status] ?? 'Unknown';
    }

    /**
     * Get the order's payment status label.
     *
     * @return string
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        $statuses = self::getPaymentStatuses();
        return $statuses[$this->payment_status] ?? 'Unknown';
    }
}
