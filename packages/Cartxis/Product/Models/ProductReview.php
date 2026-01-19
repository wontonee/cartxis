<?php

namespace Cartxis\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ProductReview extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'product_reviews';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'reviewer_name',
        'reviewer_email',
        'rating',
        'title',
        'comment',
        'status',
        'admin_reply',
        'admin_reply_by',
        'admin_replied_at',
        'helpful_count',
        'verified_purchase',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'rating' => 'integer',
        'helpful_count' => 'integer',
        'verified_purchase' => 'boolean',
        'admin_replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the product that owns the review.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who replied to the review.
     */
    public function adminReplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_reply_by');
    }

    /**
     * Scope a query to only include approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending reviews.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include rejected reviews.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to filter by rating.
     */
    public function scopeByRating($query, $rating)
    {
        if ($rating) {
            return $query->where('rating', $rating);
        }
        return $query;
    }

    /**
     * Scope a query to filter by product.
     */
    public function scopeByProduct($query, $productId)
    {
        if ($productId) {
            return $query->where('product_id', $productId);
        }
        return $query;
    }

    /**
     * Get the reviewer's display name.
     */
    public function getReviewerDisplayNameAttribute(): string
    {
        return $this->user?->name ?? $this->reviewer_name ?? 'Anonymous';
    }

    /**
     * Check if review has admin reply.
     */
    public function hasAdminReply(): bool
    {
        return !empty($this->admin_reply);
    }

    /**
     * Approve the review.
     */
    public function approve(): bool
    {
        $this->status = 'approved';
        return $this->save();
    }

    /**
     * Reject the review.
     */
    public function reject(): bool
    {
        $this->status = 'rejected';
        return $this->save();
    }

    /**
     * Set to pending status.
     */
    public function setPending(): bool
    {
        $this->status = 'pending';
        return $this->save();
    }

    /**
     * Add admin reply.
     */
    public function addReply(string $reply, int $adminId): bool
    {
        $this->admin_reply = $reply;
        $this->admin_reply_by = $adminId;
        $this->admin_replied_at = now();
        return $this->save();
    }

    /**
     * Increment helpful count.
     */
    public function incrementHelpful(): bool
    {
        $this->increment('helpful_count');
        return true;
    }
}
