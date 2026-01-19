<?php

namespace Cartxis\API\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'product_id' => $this->product_id,
            'rating' => (int) $this->rating,
            'title' => $this->title,
            'comment' => $this->comment,
            'status' => $this->status,
            'customer' => $this->whenLoaded('customer', fn() => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'avatar' => $this->customer->avatar ? \Storage::url($this->customer->avatar) : null,
            ]),
            'images' => $this->images ? collect(json_decode($this->images, true))->map(fn($img) => \Storage::url($img)) : [],
            'helpful_count' => $this->helpful_count ?? 0,
            'not_helpful_count' => $this->not_helpful_count ?? 0,
            'is_verified_purchase' => (bool) ($this->is_verified_purchase ?? true),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
