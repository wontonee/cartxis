<?php

namespace Cartxis\API\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $subtotal = $this->items->sum(fn($item) => $item->price * $item->quantity);
        $discount = $this->discount_amount ?? 0;
        $total = $subtotal - $discount;

        return [
            'id' => $this->id,
            'items' => $this->items->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'product' => new ProductResource($item->product),
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'subtotal' => (float) ($item->price * $item->quantity),
                'currency' => config('app.currency', 'USD'),
            ]),
            'summary' => [
                'items_count' => $this->items->sum('quantity'),
                'subtotal' => (float) $subtotal,
                'discount' => (float) $discount,
                'total' => (float) $total,
                'currency' => config('app.currency', 'USD'),
            ],
            'coupon' => [
                'code' => $this->coupon_code,
                'discount_amount' => (float) $discount,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
