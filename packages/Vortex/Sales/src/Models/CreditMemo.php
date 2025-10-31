<?php

namespace Vortex\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vortex\Shop\Models\Order;
use App\Models\User;

class CreditMemo extends Model
{
    protected $fillable = [
        'order_id',
        'invoice_id',
        'credit_memo_number',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'adjustment_positive',
        'adjustment_negative',
        'grand_total',
        'refund_status',
        'refund_method',
        'refunded_at',
        'restore_inventory',
        'inventory_restored_at',
        'notes',
        'admin_notes',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'adjustment_positive' => 'decimal:2',
        'adjustment_negative' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'restore_inventory' => 'boolean',
        'refunded_at' => 'datetime',
        'inventory_restored_at' => 'datetime',
    ];

    /**
     * Get the order for this credit memo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the invoice for this credit memo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the items for this credit memo
     */
    public function items(): HasMany
    {
        return $this->hasMany(CreditMemoItem::class);
    }

    /**
     * Get the user who created this credit memo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Pending credit memos
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Complete credit memos
     */
    public function scopeComplete($query)
    {
        return $query->where('status', 'complete');
    }

    /**
     * Scope: Cancelled credit memos
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope: Processed refunds
     */
    public function scopeRefunded($query)
    {
        return $query->where('refund_status', 'processed');
    }

    /**
     * Scope: Credit memos for specific order
     */
    public function scopeForOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Check if credit memo is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if credit memo is complete
     */
    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }

    /**
     * Check if credit memo is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if refund has been processed
     */
    public function isRefunded(): bool
    {
        return $this->refund_status === 'processed';
    }

    /**
     * Check if inventory has been restored
     */
    public function isInventoryRestored(): bool
    {
        return $this->inventory_restored_at !== null;
    }

    /**
     * Check if credit memo can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->isPending() && !$this->isRefunded();
    }

    /**
     * Check if credit memo can be edited
     */
    public function canBeEdited(): bool
    {
        return $this->isPending();
    }

    /**
     * Get net adjustment amount
     */
    public function getNetAdjustmentAttribute(): float
    {
        return $this->adjustment_negative - $this->adjustment_positive;
    }
}
