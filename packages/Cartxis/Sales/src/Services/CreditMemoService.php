<?php

namespace Cartxis\Sales\Services;

use Cartxis\Sales\Models\CreditMemo;
use Cartxis\Sales\Models\CreditMemoItem;
use Cartxis\Sales\Repositories\CreditMemoRepository;
use Cartxis\Shop\Models\Order;
use Cartxis\Shop\Models\OrderItem;
use Cartxis\Core\Models\EmailTemplate;
use Cartxis\Product\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreditMemoService
{
    protected CreditMemoRepository $repository;

    public function __construct(CreditMemoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create credit memo from order
     */
    public function createFromOrder(Order $order, array $items, array $data = []): CreditMemo
    {
        // Validate order can have credit memo
        if (!$order->isPaid()) {
            throw new \Exception('Cannot create credit memo for unpaid order');
        }

        // Calculate totals
        $totals = $this->calculateTotals($order, $items, $data);

        // Validate refund amount
        $maxRefundable = $this->getMaxRefundableAmount($order);
        if ($totals['grand_total'] > $maxRefundable) {
            throw new \Exception('Refund amount exceeds maximum refundable amount');
        }

        DB::beginTransaction();
        try {
            // Generate credit memo number
            $creditMemoNumber = $this->generateCreditMemoNumber();

            // Create credit memo
            $creditMemo = $this->repository->create([
                'order_id' => $order->id,
                'invoice_id' => $data['invoice_id'] ?? null,
                'credit_memo_number' => $creditMemoNumber,
                'status' => 'pending',
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'shipping_amount' => $totals['shipping_amount'],
                'discount_amount' => $totals['discount_amount'],
                'adjustment_positive' => $data['adjustment_positive'] ?? 0.00,
                'adjustment_negative' => $data['adjustment_negative'] ?? 0.00,
                'grand_total' => $totals['grand_total'],
                'refund_status' => 'pending',
                'refund_method' => $data['refund_method'] ?? 'original_payment',
                'restore_inventory' => $data['restore_inventory'] ?? true,
                'notes' => $data['notes'] ?? null,
                'admin_notes' => $data['admin_notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Create credit memo items
            foreach ($items as $orderItemId => $itemData) {
                $orderItem = OrderItem::find($orderItemId);
                
                if (!$orderItem || $orderItem->order_id !== $order->id) {
                    continue;
                }

                // Validate refund quantity
                $qtyRefunded = $itemData['qty'] ?? 0;
                if ($qtyRefunded <= 0 || $qtyRefunded > $orderItem->quantity) {
                    continue;
                }

                // Calculate item amounts
                $price = $orderItem->price;
                $taxPerItem = $orderItem->tax_amount / $orderItem->quantity;
                $discountPerItem = $orderItem->discount_amount / $orderItem->quantity;
                
                $itemTaxAmount = $taxPerItem * $qtyRefunded;
                $itemDiscountAmount = $discountPerItem * $qtyRefunded;
                $rowTotal = ($price * $qtyRefunded) + $itemTaxAmount - $itemDiscountAmount;

                CreditMemoItem::create([
                    'credit_memo_id' => $creditMemo->id,
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'product_name' => $orderItem->product_name,
                    'sku' => $orderItem->product_sku,
                    'qty_refunded' => $qtyRefunded,
                    'price' => $price,
                    'tax_amount' => $itemTaxAmount,
                    'discount_amount' => $itemDiscountAmount,
                    'row_total' => $rowTotal,
                    'restore_stock' => $itemData['restore_stock'] ?? true,
                    'stock_restored' => false,
                ]);
            }

            // Process refund immediately if requested
            if (!empty($data['process_refund'])) {
                $this->processRefund($creditMemo);
            }

            // Restore inventory immediately if requested
            if (!empty($data['restore_inventory']) && empty($data['process_refund'])) {
                $this->restoreInventory($creditMemo);
            }

            // Automatically send email notification when credit memo is created
            $this->sendCreditMemoEmail($creditMemo);

            DB::commit();

            return $creditMemo->fresh(['order', 'items']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate credit memo totals
     */
    public function calculateTotals(Order $order, array $items, array $data = []): array
    {
        $subtotal = 0;
        $taxAmount = 0;
        $discountAmount = 0;

        foreach ($items as $orderItemId => $itemData) {
            $orderItem = OrderItem::find($orderItemId);
            
            if (!$orderItem || $orderItem->order_id !== $order->id) {
                continue;
            }

            $qtyRefunded = $itemData['qty'] ?? 0;
            if ($qtyRefunded <= 0) {
                continue;
            }

            $subtotal += $orderItem->price * $qtyRefunded;
            $taxAmount += ($orderItem->tax_amount / $orderItem->quantity) * $qtyRefunded;
            $discountAmount += ($orderItem->discount_amount / $orderItem->quantity) * $qtyRefunded;
        }

        $shippingAmount = $data['refund_shipping'] ?? 0.00;
        $adjustmentPositive = $data['adjustment_positive'] ?? 0.00;
        $adjustmentNegative = $data['adjustment_negative'] ?? 0.00;

        $grandTotal = $subtotal + $taxAmount + $shippingAmount - $discountAmount - $adjustmentPositive + $adjustmentNegative;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'shipping_amount' => round($shippingAmount, 2),
            'discount_amount' => round($discountAmount, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    /**
     * Process refund to payment gateway
     */
    public function processRefund(CreditMemo $creditMemo): bool
    {
        if ($creditMemo->refund_status === 'processed') {
            throw new \Exception('Refund has already been processed');
        }

        DB::beginTransaction();
        try {
            // TODO: Implement actual payment gateway refund
            // This would integrate with Stripe, PayPal, Razorpay, etc.
            // For now, we'll just mark it as processed
            
            $creditMemo->update([
                'refund_status' => 'processed',
                'refunded_at' => now(),
            ]);

            // Update order total_refunded
            $order = $creditMemo->order;
            $order->increment('total_refunded', $creditMemo->grand_total);

            // Restore inventory if not already done
            if ($creditMemo->restore_inventory && !$creditMemo->isInventoryRestored()) {
                $this->restoreInventory($creditMemo);
            }

            // Mark as complete
            $creditMemo->update(['status' => 'complete']);

            // Send email notification
            $this->sendCreditMemoEmail($creditMemo);

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Mark refund as failed
            $creditMemo->update(['refund_status' => 'failed']);
            
            throw $e;
        }
    }

    /**
     * Restore inventory for credit memo items
     */
    public function restoreInventory(CreditMemo $creditMemo): bool
    {
        if ($creditMemo->isInventoryRestored()) {
            return true;
        }

        DB::beginTransaction();
        try {
            foreach ($creditMemo->items as $item) {
                if (!$item->restore_stock || $item->stock_restored) {
                    continue;
                }

                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('quantity', $item->qty_refunded);
                    $item->update(['stock_restored' => true]);
                }
            }

            $creditMemo->update(['inventory_restored_at' => now()]);

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark credit memo as complete
     */
    public function markAsComplete(int $id): bool
    {
        $creditMemo = $this->repository->find($id);
        
        if (!$creditMemo) {
            throw new \Exception('Credit memo not found');
        }

        if ($creditMemo->isComplete()) {
            return true;
        }

        return $creditMemo->update(['status' => 'complete']);
    }

    /**
     * Cancel credit memo
     */
    public function cancel(int $id, string $reason = null): bool
    {
        $creditMemo = $this->repository->find($id);
        
        if (!$creditMemo) {
            throw new \Exception('Credit memo not found');
        }

        if ($creditMemo->isRefunded()) {
            throw new \Exception('Cannot cancel refunded credit memo');
        }

        return $creditMemo->update([
            'status' => 'cancelled',
            'admin_notes' => ($creditMemo->admin_notes ?? '') . "\n\nCancelled: " . $reason,
        ]);
    }

    /**
     * Update credit memo
     */
    public function update(int $id, array $data): bool
    {
        $creditMemo = $this->repository->find($id);
        
        if (!$creditMemo) {
            throw new \Exception('Credit memo not found');
        }

        if (!$creditMemo->canBeEdited()) {
            throw new \Exception('Credit memo cannot be edited');
        }

        // Only allow editing certain fields
        $allowedFields = ['notes', 'admin_notes'];
        
        if ($creditMemo->refund_status === 'pending') {
            $allowedFields = array_merge($allowedFields, [
                'adjustment_positive',
                'adjustment_negative',
                'refund_method'
            ]);
        }

        $updateData = array_intersect_key($data, array_flip($allowedFields));

        // Recalculate grand total if adjustments changed
        if (isset($updateData['adjustment_positive']) || isset($updateData['adjustment_negative'])) {
            $adjustmentPositive = $updateData['adjustment_positive'] ?? $creditMemo->adjustment_positive;
            $adjustmentNegative = $updateData['adjustment_negative'] ?? $creditMemo->adjustment_negative;
            
            $updateData['grand_total'] = $creditMemo->subtotal 
                + $creditMemo->tax_amount 
                + $creditMemo->shipping_amount 
                - $creditMemo->discount_amount 
                - $adjustmentPositive 
                + $adjustmentNegative;
        }

        return $creditMemo->update($updateData);
    }

    /**
     * Delete credit memo
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Get refundable items for order
     */
    public function getRefundableItems(Order $order): Collection
    {
        $creditMemos = $this->repository->getForOrder($order->id);
        
        $refundedQuantities = [];
        foreach ($creditMemos as $creditMemo) {
            if ($creditMemo->status === 'cancelled') {
                continue;
            }
            
            foreach ($creditMemo->items as $item) {
                $orderItemId = $item->order_item_id;
                $refundedQuantities[$orderItemId] = ($refundedQuantities[$orderItemId] ?? 0) + $item->qty_refunded;
            }
        }

        return $order->items->map(function ($orderItem) use ($refundedQuantities) {
            $refundedQty = $refundedQuantities[$orderItem->id] ?? 0;
            $availableQty = $orderItem->quantity - $refundedQty;

            return [
                'order_item_id' => $orderItem->id,
                'product_id' => $orderItem->product_id,
                'product_name' => $orderItem->product_name,
                'sku' => $orderItem->product_sku,
                'qty_ordered' => $orderItem->quantity,
                'qty_refunded' => $refundedQty,
                'qty_available' => $availableQty,
                'price' => $orderItem->price,
                'tax_amount' => $orderItem->tax_amount,
                'discount_amount' => $orderItem->discount_amount,
                'row_total' => $orderItem->total,
            ];
        })->filter(function ($item) {
            return $item['qty_available'] > 0;
        })->values();
    }

    /**
     * Calculate maximum refundable amount for order
     */
    public function getMaxRefundableAmount(Order $order): float
    {
        $totalRefunded = $this->repository->getTotalRefundedForOrder($order->id);
        return max(0, $order->total - $totalRefunded);
    }

    /**
     * Generate unique credit memo number
     */
    protected function generateCreditMemoNumber(): string
    {
        $date = now()->format('Ymd');
        $random = str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        
        return "CRM-{$date}-{$random}";
    }

    /**
     * Send credit memo email to customer
     */
    public function sendCreditMemoEmail(CreditMemo $creditMemo): void
    {
        if (!$creditMemo->order->customer_email) {
            return;
        }

        $template = EmailTemplate::findByCode('credit_memo_created');
        if (!$template || !$template->is_active) {
            return;
        }

        $data = [
            'customer_name' => $creditMemo->order->customer_name ?? 'Valued Customer',
            'credit_memo_number' => $creditMemo->credit_memo_number,
            'order_number' => $creditMemo->order->order_number,
            'refund_amount' => '₹' . number_format($creditMemo->grand_total, 2),
            'refund_method' => ucfirst(str_replace('_', ' ', $creditMemo->refund_method ?? 'original payment')),
            'issue_date' => $creditMemo->created_at->format('F d, Y'),
            'store_name' => config('app.name'),
            'store_url' => config('app.url'),
            'year' => date('Y'),
        ];

        $template->send($creditMemo->order->customer_email, $data);
    }
}
