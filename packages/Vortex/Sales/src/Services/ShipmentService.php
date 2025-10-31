<?php

namespace Vortex\Sales\Services;

use Vortex\Sales\Models\Shipment;
use Vortex\Sales\Models\ShipmentItem;
use Vortex\Shop\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Exception;


class ShipmentService
{
    /**
     * Get paginated shipments with filters
     */
    public function getShipments(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Shipment::with(['order', 'order.user', 'shipmentItems.orderItem.product']);

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhere('carrier', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($q) use ($search) {
                        $q->where('order_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('order.user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->withStatus($filters['status']);
        }

        // Date range filter
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $dateFrom = $filters['date_from'] ?? null;
            $dateTo = $filters['date_to'] ?? null;
            $query->dateRange($dateFrom, $dateTo);
        }

        // Order by
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create shipment from order
     */
    public function createFromOrder(Order $order, array $data): Shipment
    {
        DB::beginTransaction();

        try {
            // Validate order can be shipped
            if (!$order->canBeShipped()) {
                throw new Exception('This order cannot be shipped');
            }

            // Validate items
            if (empty($data['items']) || !is_array($data['items'])) {
                throw new Exception('At least one item is required for shipment');
            }

            // Create shipment
            $shipmentData = [
                'order_id' => $order->id,
                'status' => Shipment::STATUS_PENDING,
                'carrier' => $data['carrier'] ?? null,
                'tracking_number' => $data['tracking_number'] ?? null,
                'tracking_url' => $data['tracking_url'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            $shipment = Shipment::create($shipmentData);

            // Create shipment items
            foreach ($data['items'] as $item) {
                $orderItemId = $item['order_item_id'];
                $quantity = $item['quantity'];

                // Validate quantity
                $remainingQty = $order->getRemainingQuantityToShip($orderItemId);
                if ($quantity > $remainingQty) {
                    throw new Exception("Quantity exceeds remaining quantity to ship for item #{$orderItemId}");
                }

                ShipmentItem::create([
                    'shipment_id' => $shipment->id,
                    'order_item_id' => $orderItemId,
                    'quantity' => $quantity,
                ]);
            }

            DB::commit();

            return $shipment->load(['order', 'shipmentItems.orderItem']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update shipment
     */
    public function update(int $shipmentId, array $data): bool
    {
        DB::beginTransaction();

        try {
            $shipment = Shipment::findOrFail($shipmentId);

            if (!$shipment->canEdit()) {
                throw new Exception('This shipment cannot be edited');
            }

            // Update basic info
            $updateData = [];
            
            if (isset($data['carrier'])) {
                $updateData['carrier'] = $data['carrier'];
            }
            
            if (isset($data['tracking_number'])) {
                $updateData['tracking_number'] = $data['tracking_number'];
            }
            
            if (isset($data['tracking_url'])) {
                $updateData['tracking_url'] = $data['tracking_url'];
            }
            
            if (isset($data['notes'])) {
                $updateData['notes'] = $data['notes'];
            }

            if (!empty($updateData)) {
                $shipment->update($updateData);
            }

            // Update items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                $shipment->shipmentItems()->delete();

                // Create new items
                foreach ($data['items'] as $item) {
                    $orderItemId = $item['order_item_id'];
                    $quantity = $item['quantity'];

                    // Validate quantity (excluding current shipment)
                    $order = $shipment->order;
                    $remainingQty = $order->getRemainingQuantityToShip($orderItemId);
                    
                    // Add back the current shipment's quantity for this item
                    $currentShipmentItem = $shipment->shipmentItems()
                        ->where('order_item_id', $orderItemId)
                        ->first();
                    
                    if ($currentShipmentItem) {
                        $remainingQty += $currentShipmentItem->quantity;
                    }

                    if ($quantity > $remainingQty) {
                        throw new Exception("Quantity exceeds remaining quantity to ship for item #{$orderItemId}");
                    }

                    ShipmentItem::create([
                        'shipment_id' => $shipment->id,
                        'order_item_id' => $orderItemId,
                        'quantity' => $quantity,
                    ]);
                }
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update tracking information
     */
    public function updateTracking(int $shipmentId, array $data): bool
    {
        $shipment = Shipment::findOrFail($shipmentId);

        if (!$shipment->canEdit()) {
            throw new Exception('This shipment cannot be edited');
        }

        return $shipment->update([
            'tracking_number' => $data['tracking_number'] ?? $shipment->tracking_number,
            'tracking_url' => $data['tracking_url'] ?? $shipment->tracking_url,
            'carrier' => $data['carrier'] ?? $shipment->carrier,
        ]);
    }

    /**
     * Mark shipment as shipped
     */
    public function markAsShipped(int $shipmentId): bool
    {
        DB::beginTransaction();

        try {
            $shipment = Shipment::with(['order', 'shipmentItems.orderItem.product'])->findOrFail($shipmentId);

            if (!$shipment->isPending()) {
                throw new Exception('Only pending shipments can be marked as shipped');
            }

            $shipment->update([
                'status' => Shipment::STATUS_SHIPPED,
                'shipped_at' => now(),
            ]);

            // Send shipment shipped email
            $this->sendShipmentShippedEmail($shipment);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update shipment status
     */
    public function updateStatus(int $shipmentId, string $status): bool
    {
        DB::beginTransaction();

        try {
            // Load relationships for email
            $shipment = Shipment::with(['order', 'shipmentItems.orderItem.product'])->findOrFail($shipmentId);

            // Validate status transition
            $validStatuses = array_keys(Shipment::getStatuses());
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Invalid shipment status');
            }

            // Don't allow changing from delivered
            if ($shipment->isDelivered()) {
                throw new Exception('Delivered shipments cannot be changed');
            }

            // Don't allow changing cancelled status
            if ($shipment->isCancelled()) {
                throw new Exception('Cancelled shipments cannot be changed');
            }

            $updateData = ['status' => $status];

            // Set shipped_at when marking as shipped
            if ($status === Shipment::STATUS_SHIPPED && !$shipment->shipped_at) {
                $updateData['shipped_at'] = now();
            }

            // Set delivered_at when marking as delivered
            if ($status === Shipment::STATUS_DELIVERED) {
                $updateData['delivered_at'] = now();
            }

            $shipment->update($updateData);

            // Send emails based on status change
            if ($status === Shipment::STATUS_SHIPPED) {
                $this->sendShipmentShippedEmail($shipment);
            } elseif ($status === Shipment::STATUS_DELIVERED) {
                $this->sendShipmentDeliveredEmail($shipment);
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel shipment
     */
    public function cancel(int $shipmentId, string $reason = null): bool
    {
        DB::beginTransaction();

        try {
            $shipment = Shipment::findOrFail($shipmentId);

            if (!$shipment->canCancel()) {
                throw new Exception('This shipment cannot be cancelled');
            }

            $updateData = [
                'status' => Shipment::STATUS_CANCELLED,
            ];

            if ($reason) {
                $updateData['notes'] = $shipment->notes 
                    ? $shipment->notes . "\n\nCancellation reason: " . $reason
                    : "Cancellation reason: " . $reason;
            }

            $shipment->update($updateData);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get shipment statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => Shipment::count(),
            'pending' => Shipment::where('status', Shipment::STATUS_PENDING)->count(),
            'shipped' => Shipment::where('status', Shipment::STATUS_SHIPPED)->count(),
            'in_transit' => Shipment::where('status', Shipment::STATUS_IN_TRANSIT)->count(),
            'out_for_delivery' => Shipment::where('status', Shipment::STATUS_OUT_FOR_DELIVERY)->count(),
            'delivered' => Shipment::where('status', Shipment::STATUS_DELIVERED)->count(),
            'failed' => Shipment::where('status', Shipment::STATUS_FAILED)->count(),
            'cancelled' => Shipment::where('status', Shipment::STATUS_CANCELLED)->count(),
        ];
    }

    /**
     * Get shipments for an order
     */
    public function getShipmentsForOrder(int $orderId): Collection
    {
        return Shipment::where('order_id', $orderId)
            ->with(['shipmentItems.orderItem.product'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Send shipment shipped email
     */
    protected function sendShipmentShippedEmail(Shipment $shipment): void
    {
        if (!$shipment->order->customer_email) {
            return;
        }

        $template = \Vortex\Core\Models\EmailTemplate::findByCode('shipment_shipped');
        if (!$template || !$template->is_active) {
            return;
        }

        $data = [
            'customer_name' => $shipment->order->user->name ?? 'Customer',
            'order_number' => $shipment->order->order_number,
            'shipment_number' => $shipment->shipment_number,
            'tracking_number' => $shipment->tracking_number ?? '',
            'shipping_address' => $shipment->order->shipping_address ?? '',
            'store_name' => config('app.name'),
        ];

        $template->send($shipment->order->customer_email, $data);
    }

    /**
     * Send shipment delivered email
     */
    protected function sendShipmentDeliveredEmail(Shipment $shipment): void
    {
        if (!$shipment->order->customer_email) {
            return;
        }

        $template = \Vortex\Core\Models\EmailTemplate::findByCode('shipment_delivered');
        if (!$template || !$template->is_active) {
            return;
        }

        $data = [
            'customer_name' => $shipment->order->user->name ?? 'Customer',
            'order_number' => $shipment->order->order_number,
            'shipment_number' => $shipment->shipment_number,
            'delivered_at' => $shipment->delivered_at ? $shipment->delivered_at->format('F d, Y') : now()->format('F d, Y'),
            'store_name' => config('app.name'),
        ];

        $template->send($shipment->order->customer_email, $data);
    }
}
