<?php

namespace Vortex\API\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => (float) $this->price,
            'special_price' => $this->special_price ? (float) $this->special_price : null,
            'final_price' => (float) ($this->special_price ?: $this->price),
            'discount_percentage' => $this->special_price 
                ? round((($this->price - $this->special_price) / $this->price) * 100, 2)
                : 0,
            'currency' => config('app.currency', 'USD'),
            'stock_status' => $this->stock_status,
            'quantity' => $this->quantity ?? 0,
            'in_stock' => $this->quantity > 0,
            'is_featured' => (bool) $this->featured,
            'is_new' => (bool) $this->new,
            'weight' => $this->weight,
            'dimensions' => [
                'length' => $this->length,
                'width' => $this->width,
                'height' => $this->height,
            ],
            'brand' => $this->whenLoaded('brand', fn() => $this->brand ? [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
                'slug' => $this->brand->slug,
                'logo' => $this->brand->logo ? \Storage::url($this->brand->logo) : null,
            ] : null),
            'categories' => $this->whenLoaded('categories', fn() => 
                CategoryResource::collection($this->categories)
            ),
            'images' => $this->whenLoaded('images', fn() => 
                $this->images->map(fn($image) => [
                    'id' => $image->id,
                    'path' => url(\Storage::url($image->path)),
                    'thumbnail' => url(\Storage::url($image->path)), // TODO: Generate thumbnails
                    'position' => $image->position,
                    'is_main' => (bool) $image->is_main,
                ])
            ),
            'variants' => $this->whenLoaded('variants', fn() => 
                $this->variants->map(fn($variant) => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'name' => $variant->name,
                    'price' => (float) $variant->price,
                    'quantity' => $variant->quantity,
                    'in_stock' => $variant->quantity > 0,
                ])
            ),
            'attributes' => $this->whenLoaded('attributes', fn() => 
                $this->attributes->map(fn($attr) => [
                    'id' => $attr->id,
                    'code' => $attr->code,
                    'name' => $attr->name,
                    'value' => $attr->pivot->value,
                ])
            ),
            'reviews_summary' => [
                'average_rating' => $this->reviews_avg_rating ?? 0,
                'total_reviews' => $this->reviews_count ?? 0,
            ],
            'recent_reviews' => $this->whenLoaded('reviews', fn() => 
                ReviewResource::collection($this->reviews->take(5))
            ),
            'meta' => [
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords' => $this->meta_keywords,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
