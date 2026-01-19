<?php

namespace Cartxis\Sales\Services;

use Cartxis\Sales\Models\Invoice;
use Cartxis\Sales\Repositories\InvoiceRepository;
use Cartxis\Shop\Models\Order;
use Illuminate\Support\Facades\DB;
use Exception;

class InvoiceService
{
    protected InvoiceRepository $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Create invoice from order
     */
    public function createFromOrder(Order $order, array $additionalData = []): Invoice
    {
        DB::beginTransaction();

        try {
            $invoiceData = [
                'order_id' => $order->id,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'status' => 'pending',
                'issue_date' => now(),
                'due_date' => $additionalData['due_date'] ?? now()->addDays(30),
                'subtotal' => $order->subtotal,
                'tax' => $order->tax,
                'total' => $order->total,
                'notes' => $additionalData['notes'] ?? null,
            ];

            $invoice = $this->invoiceRepository->create($invoiceData);

            DB::commit();

            return $invoice;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update invoice
     */
    public function update(int $invoiceId, array $data): bool
    {
        DB::beginTransaction();

        try {
            $updated = $this->invoiceRepository->update($invoiceId, $data);

            DB::commit();

            return $updated;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(int $invoiceId): bool
    {
        $invoice = $this->invoiceRepository->find($invoiceId);

        if (!$invoice) {
            throw new Exception('Invoice not found');
        }

        if (!$invoice->isPending()) {
            throw new Exception('Only pending invoices can be marked as sent');
        }

        return $invoice->markAsSent();
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(int $invoiceId): bool
    {
        DB::beginTransaction();

        try {
            $invoice = $this->invoiceRepository->find($invoiceId);

            if (!$invoice) {
                throw new Exception('Invoice not found');
            }

            if ($invoice->isCancelled()) {
                throw new Exception('Cannot mark cancelled invoice as paid');
            }

            $updated = $invoice->markAsPaid();

            // Update order payment status
            $invoice->order->update(['payment_status' => 'paid']);

            DB::commit();

            return $updated;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel invoice
     */
    public function cancel(int $invoiceId): bool
    {
        $invoice = $this->invoiceRepository->find($invoiceId);

        if (!$invoice) {
            throw new Exception('Invoice not found');
        }

        if ($invoice->isPaid()) {
            throw new Exception('Cannot cancel paid invoice');
        }

        return $invoice->cancel();
    }

    /**
     * Delete invoice
     */
    public function delete(int $invoiceId): bool
    {
        $invoice = $this->invoiceRepository->find($invoiceId);

        if (!$invoice) {
            throw new Exception('Invoice not found');
        }

        if ($invoice->isPaid()) {
            throw new Exception('Cannot delete paid invoice');
        }

        return $this->invoiceRepository->delete($invoiceId);
    }

    /**
     * Get invoice with related data
     */
    public function getInvoiceDetails(int $invoiceId): ?Invoice
    {
        return $this->invoiceRepository->find($invoiceId);
    }

    /**
     * Get invoices for order
     */
    public function getOrderInvoices(int $orderId)
    {
        return $this->invoiceRepository->getByOrder($orderId);
    }

    /**
     * Check if order has invoice
     */
    public function orderHasInvoice(int $orderId): bool
    {
        return $this->invoiceRepository->getByOrder($orderId)->isNotEmpty();
    }

    /**
     * Generate invoice data for PDF/display
     */
    public function generateInvoiceData(Invoice $invoice): array
    {
        $order = $invoice->order;
        $shippingAddress = $order->addresses->where('type', 'shipping')->first();
        $billingAddress = $order->addresses->where('type', 'billing')->first();

        return [
            'invoice' => [
                'number' => $invoice->invoice_number,
                'issue_date' => $invoice->issue_date->format('F d, Y'),
                'due_date' => $invoice->due_date ? $invoice->due_date->format('F d, Y') : null,
                'status' => $invoice->status,
                'notes' => $invoice->notes,
            ],
            'order' => [
                'number' => $order->order_number,
                'date' => $order->created_at->format('F d, Y'),
            ],
            'customer' => [
                'name' => $order->user ? $order->user->name : 'Guest',
                'email' => $order->customer_email ?? $order->user?->email,
                'phone' => $order->customer_phone,
            ],
            'billing_address' => $billingAddress ? [
                'full_name' => $billingAddress->full_name,
                'address_line1' => $billingAddress->address_line1,
                'address_line2' => $billingAddress->address_line2,
                'city' => $billingAddress->city,
                'state' => $billingAddress->state,
                'postal_code' => $billingAddress->postal_code,
                'country' => $billingAddress->country,
                'phone' => $billingAddress->phone,
            ] : null,
            'shipping_address' => $shippingAddress ? [
                'full_name' => $shippingAddress->full_name,
                'address_line1' => $shippingAddress->address_line1,
                'address_line2' => $shippingAddress->address_line2,
                'city' => $shippingAddress->city,
                'state' => $shippingAddress->state,
                'postal_code' => $shippingAddress->postal_code,
                'country' => $shippingAddress->country,
                'phone' => $shippingAddress->phone,
            ] : null,
            'items' => $order->items->map(function ($item) {
                return [
                    'product_name' => $item->product_name,
                    'sku' => $item->product_sku,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                ];
            })->toArray(),
            'totals' => [
                'subtotal' => $invoice->subtotal,
                'tax' => $invoice->tax,
                'shipping' => $order->shipping_cost,
                'discount' => $order->discount,
                'total' => $invoice->total,
            ],
        ];
    }
}
