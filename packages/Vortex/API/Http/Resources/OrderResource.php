<?php

namespace Vortex\API\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->increment_id,
            'status' => $this->status,
            'status_label' => ucfirst($this->status),
            'items' => $this->whenLoaded('items', fn() => 
                $this->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product' => new ProductResource($item->product),
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->total,
                    'tax_amount' => (float) ($item->tax_amount ?? 0),
                    'discount_amount' => (float) ($item->discount_amount ?? 0),
                ])
            ),
            'totals' => [
                'subtotal' => (float) $this->sub_total,
                'shipping_cost' => (float) ($this->shipping_amount ?? 0),
                'tax' => (float) ($this->tax_amount ?? 0),
                'discount' => (float) ($this->discount_amount ?? 0),
                'grand_total' => (float) $this->grand_total,
                'currency' => $this->currency_code ?? config('app.currency', 'USD'),
            ],
            'payment' => [
                'method' => $this->payment_method,
                'method_title' => $this->payment_method_title,
                'status' => $this->payment_status ?? 'pending',
            ],
            'shipping_address' => $this->whenLoaded('shippingAddress', fn() => 
                new AddressResource($this->shippingAddress)
            ),
            'billing_address' => $this->whenLoaded('billingAddress', fn() => 
                new AddressResource($this->billingAddress)
            ),
            'shipments' => $this->whenLoaded('shipments', fn() => 
                $this->shipments->map(fn($shipment) => [
                    'id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'carrier' => $shipment->carrier,
                    'status' => $shipment->status,
                    'shipped_at' => $shipment->shipped_at?->toIso8601String(),
                    'estimated_delivery' => $shipment->estimated_delivery?->toIso8601String(),
                ])
            ),
            'customer_note' => $this->customer_note,
            'coupon_code' => $this->coupon_code,
            'can_cancel' => in_array($this->status, ['pending', 'processing']),
            'can_reorder' => in_array($this->status, ['completed', 'cancelled']),
            'ordered_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
