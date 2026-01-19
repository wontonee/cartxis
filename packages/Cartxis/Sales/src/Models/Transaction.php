<?php

namespace Cartxis\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cartxis\Shop\Models\Order;

class Transaction extends Model
{
    protected $fillable = [
        'order_id',
        'invoice_id',
        'credit_memo_id',
        'transaction_number',
        'type',
        'payment_method',
        'gateway',
        'gateway_transaction_id',
        'amount',
        'status',
        'response_data',
        'notes',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'response_data' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order that owns the transaction.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the invoice that this transaction is related to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the credit memo that this transaction is related to.
     */
    public function creditMemo(): BelongsTo
    {
        return $this->belongsTo(CreditMemo::class);
    }

    /**
     * Scope a query to only include transactions of a given type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include transactions with a given status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include transactions from a specific gateway.
     */
    public function scopeFromGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope a query to only include completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if transaction is a payment.
     */
    public function isPayment(): bool
    {
        return $this->type === 'payment';
    }

    /**
     * Check if transaction is a refund.
     */
    public function isRefund(): bool
    {
        return $this->type === 'refund';
    }

    /**
     * Check if transaction is an authorization.
     */
    public function isAuthorization(): bool
    {
        return $this->type === 'authorization';
    }

    /**
     * Check if transaction is a capture.
     */
    public function isCapture(): bool
    {
        return $this->type === 'capture';
    }

    /**
     * Check if transaction is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction has failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction can be retried.
     */
    public function canBeRetried(): bool
    {
        return $this->status === 'failed' && in_array($this->type, ['payment', 'capture']);
    }

    /**
     * Check if transaction can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return $this->status === 'completed' && 
               $this->type === 'payment' && 
               !$this->hasRefund();
    }

    /**
     * Check if transaction has a refund.
     */
    public function hasRefund(): bool
    {
        return Transaction::where('order_id', $this->order_id)
            ->where('type', 'refund')
            ->where('gateway_transaction_id', $this->gateway_transaction_id)
            ->exists();
    }

    /**
     * Get the formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->amount, 2);
    }

    /**
     * Generate a unique transaction number.
     */
    public static function generateTransactionNumber(): string
    {
        $date = now()->format('Ymd');
        $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        
        return "TXN-{$date}-{$random}";
    }
}
