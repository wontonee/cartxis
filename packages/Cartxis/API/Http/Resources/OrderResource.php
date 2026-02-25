<?php

namespace Cartxis\API\Http\Resources;

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
        $currency = \Cartxis\Core\Models\Currency::getDefault();

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'status_label' => ucfirst($this->status),
            'items' => $this->whenLoaded('items', fn() => 
                $this->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product' => $item->product ? new ProductResource($item->product) : null,
                    'sku' => $item->product_sku,
                    'name' => $item->product_name,
                    'image' => $item->product_image,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->total,
                    'tax_amount' => (float) ($item->tax_amount ?? 0),
                    'discount_amount' => (float) ($item->discount_amount ?? 0),
                ])
            ),
            'totals' => [
                'subtotal' => (float) ($this->subtotal ?? 0),
                'shipping_cost' => (float) ($this->shipping_cost ?? 0),
                'tax' => (float) ($this->tax ?? 0),
                'discount' => (float) ($this->discount ?? 0),
                'grand_total' => (float) ($this->total ?? 0),
                'currency' => $currency?->code ?? 'INR',
                'currency_symbol' => $currency?->symbol ?? '₹',
            ],
            'payment' => [
                'method' => $this->payment_method,
                'method_title' => ucwords(str_replace('_', ' ', $this->payment_method ?? '')),
                'status' => $this->payment_status ?? 'pending',
            ],
            'shipping_address' => $this->whenLoaded('shippingAddress', fn() => 
                $this->shippingAddress ? new AddressResource($this->shippingAddress) : null
            ),
            'billing_address' => $this->whenLoaded('billingAddress', fn() => 
                $this->billingAddress ? new AddressResource($this->billingAddress) : null
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
            'customer_note' => $this->notes,
            'can_cancel' => in_array($this->status, ['pending', 'processing']),
            'can_reorder' => in_array($this->status, ['completed', 'cancelled']),
            'ordered_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
