<?php

namespace Cartxis\API\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (method_exists($this->resource, 'translate')) {
            $this->resource->loadMissing('translations');
            $this->resource->translate(app()->getLocale());
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'locale' => app()->getLocale(),
            'image' => $this->image ? \Storage::url($this->image) : null,
            'icon' => $this->icon,
            'position' => $this->position,
            'products_count' => $this->products_count ?? 0,
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', fn () => [
                'id' => $this->parent->id,
                'name' => $this->parent->name,
                'slug' => $this->parent->slug,
            ]),
            'children' => $this->whenLoaded('children', fn () =>
                self::collection($this->children)
            ),
            'meta' => [
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
