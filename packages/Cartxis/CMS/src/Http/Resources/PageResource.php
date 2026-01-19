<?php

declare(strict_types=1);

namespace Cartxis\CMS\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url_key' => $this->url_key,
            'content' => $this->content,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'channel_id' => $this->channel_id,
            'url' => $this->url,
            'created_by' => $this->when($this->creator, [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ]),
            'updated_by' => $this->when($this->updater, [
                'id' => $this->updater?->id,
                'name' => $this->updater?->name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
