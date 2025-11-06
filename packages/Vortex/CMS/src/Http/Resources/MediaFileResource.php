<?php

namespace Vortex\CMS\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaFileResource extends JsonResource
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
            'filename' => $this->filename,
            'original_filename' => $this->original_filename,
            'path' => $this->path,
            'url' => $this->url,
            'disk' => $this->disk,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'formatted_size' => $this->formatted_size,
            'extension' => $this->extension,
            'alt_text' => $this->alt_text,
            'title' => $this->title,
            'description' => $this->description,
            'width' => $this->width,
            'height' => $this->height,
            'thumbnails' => $this->thumbnails,
            'thumbnail_url' => $this->thumbnail_url,
            'used_count' => $this->used_count,
            'is_image' => $this->is_image,
            'is_video' => $this->is_video,
            'is_document' => $this->is_document,
            'folder' => $this->whenLoaded('folder', function () {
                return [
                    'id' => $this->folder->id,
                    'name' => $this->folder->name,
                    'path' => $this->folder->path,
                ];
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            'updater' => $this->whenLoaded('updater', function () {
                return [
                    'id' => $this->updater->id,
                    'name' => $this->updater->name,
                ];
            }),
            'usages' => $this->whenLoaded('usages', function () {
                return $this->usages->map(function ($usage) {
                    return [
                        'id' => $usage->id,
                        'context' => $usage->context,
                        'usable_type' => class_basename($usage->usable_type),
                        'usable' => $usage->usable ? [
                            'id' => $usage->usable->id,
                            'title' => $usage->usable->title ?? $usage->usable->name ?? 'Unknown',
                        ] : null,
                    ];
                });
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
