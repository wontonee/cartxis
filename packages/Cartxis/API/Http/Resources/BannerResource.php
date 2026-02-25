<?php

namespace Cartxis\API\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray($request)
    {
        $content = $this->content_array ?? [];

        $image = $content['image'] ?? null;
        $imageUrl = null;
        if (!empty($image)) {
            $imageUrl = str_starts_with($image, 'http://') || str_starts_with($image, 'https://')
                ? $image
                : url($image);
        }

        $link = $content['link'] ?? ($content['url'] ?? null);
        $linkUrl = null;
        if (!empty($link)) {
            $linkUrl = str_starts_with($link, 'http://') || str_starts_with($link, 'https://')
                ? $link
                : url($link);
        }

        $label = $content['label'] ?? ($content['badge'] ?? ($content['tag'] ?? null));

        return [
            'id' => $this->id,
            'identifier' => $this->identifier,
            'title' => $this->title,
            'type' => $this->type,
            'status' => $this->status,
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'is_visible' => $this->isVisible(),

            // Normalized banner fields for mobile
            'label' => $label,
            'subtitle' => $content['subtitle'] ?? null,
            'text' => $content['text'] ?? ($content['description'] ?? null),
            'button_text' => $content['button_text'] ?? null,
            'overlay_color' => $content['overlay_color'] ?? null,
            'image' => $image,
            'image_path' => $image,
            'image_url' => $imageUrl,
            'link' => $link,
            'link_path' => $link,
            'link_url' => $linkUrl,

            // Keep full content for flexibility
            'content' => $this->content,
            'content_array' => $content,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
