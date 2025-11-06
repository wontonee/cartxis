<?php

namespace Vortex\CMS\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Vortex\CMS\Models\MediaFile;
use Vortex\CMS\Models\MediaFolder;

class MediaService
{
    protected ImageManager $imageManager;
    protected array $thumbnailSizes = [150, 600, 1200];

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Upload a file and create media record
     */
    public function upload(UploadedFile $file, ?int $folderId = null, array $metadata = []): MediaFile
    {
        // Generate unique filename
        $filename = $this->generateUniqueFilename($file);
        $extension = $file->getClientOriginalExtension();
        
        // Store file
        $disk = config('cms.media.disk', 'public');
        $path = $file->storeAs('media', $filename, $disk);

        // Get file info
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Create media record
        $mediaFile = MediaFile::create([
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => $disk,
            'mime_type' => $mimeType,
            'size' => $size,
            'extension' => $extension,
            'folder_id' => $folderId,
            'alt_text' => $metadata['alt_text'] ?? null,
            'title' => $metadata['title'] ?? null,
            'description' => $metadata['description'] ?? null,
        ]);

        // Process image if it's an image file
        if (str_starts_with($mimeType, 'image/')) {
            $this->processImage($mediaFile);
        }

        return $mediaFile->fresh();
    }

    /**
     * Process image: get dimensions and generate thumbnails
     */
    protected function processImage(MediaFile $mediaFile): void
    {
        try {
            $fullPath = Storage::disk($mediaFile->disk)->path($mediaFile->path);
            $image = $this->imageManager->read($fullPath);

            // Store dimensions
            $mediaFile->width = $image->width();
            $mediaFile->height = $image->height();

            // Generate thumbnails
            $thumbnails = $this->generateThumbnails($mediaFile, $image);
            $mediaFile->thumbnails = $thumbnails;

            $mediaFile->save();
        } catch (\Exception $e) {
            \Log::error('Failed to process image: ' . $e->getMessage());
        }
    }

    /**
     * Generate thumbnails for an image
     */
    protected function generateThumbnails(MediaFile $mediaFile, $image): array
    {
        $thumbnails = [];
        $disk = $mediaFile->disk;
        $directory = dirname($mediaFile->path);
        $filename = pathinfo($mediaFile->filename, PATHINFO_FILENAME);
        $extension = pathinfo($mediaFile->filename, PATHINFO_EXTENSION);

        foreach ($this->thumbnailSizes as $size) {
            try {
                // Skip if image is smaller than thumbnail size
                if ($image->width() <= $size && $image->height() <= $size) {
                    continue;
                }

                // Resize image maintaining aspect ratio
                $thumbnail = clone $image;
                $thumbnail->scale(width: $size);

                // Generate thumbnail path
                $thumbnailFilename = "{$filename}-{$size}.{$extension}";
                $thumbnailPath = "{$directory}/{$thumbnailFilename}";

                // Save thumbnail
                $thumbnailFullPath = Storage::disk($disk)->path($thumbnailPath);
                $thumbnail->save($thumbnailFullPath);

                $thumbnails[(string)$size] = $thumbnailPath;
            } catch (\Exception $e) {
                \Log::error("Failed to generate {$size}px thumbnail: " . $e->getMessage());
            }
        }

        return $thumbnails;
    }

    /**
     * Update media file metadata
     */
    public function update(MediaFile $mediaFile, array $data): MediaFile
    {
        $mediaFile->update([
            'alt_text' => $data['alt_text'] ?? $mediaFile->alt_text,
            'title' => $data['title'] ?? $mediaFile->title,
            'description' => $data['description'] ?? $mediaFile->description,
            'folder_id' => $data['folder_id'] ?? $mediaFile->folder_id,
        ]);

        return $mediaFile->fresh();
    }

    /**
     * Delete media file and physical files
     */
    public function delete(MediaFile $mediaFile, bool $force = false): bool
    {
        if ($force) {
            return $mediaFile->forceDelete();
        }

        return $mediaFile->delete();
    }

    /**
     * Bulk delete media files
     */
    public function bulkDelete(array $ids, bool $force = false): int
    {
        $query = MediaFile::whereIn('id', $ids);

        if ($force) {
            // Get files first to delete physical files
            $files = $query->get();
            foreach ($files as $file) {
                $file->forceDelete();
            }
            return $files->count();
        }

        return $query->delete();
    }

    /**
     * Move files to a different folder
     */
    public function moveToFolder(array $ids, ?int $folderId): int
    {
        return MediaFile::whereIn('id', $ids)->update(['folder_id' => $folderId]);
    }

    /**
     * Generate unique filename
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $uuid = Str::uuid();
        
        return "{$uuid}.{$extension}";
    }

    /**
     * Create a folder
     */
    public function createFolder(string $name, ?int $parentId = null): MediaFolder
    {
        return MediaFolder::create([
            'name' => $name,
            'parent_id' => $parentId,
            'sort_order' => MediaFolder::where('parent_id', $parentId)->max('sort_order') + 1,
        ]);
    }

    /**
     * Update folder
     */
    public function updateFolder(MediaFolder $folder, array $data): MediaFolder
    {
        $folder->update([
            'name' => $data['name'] ?? $folder->name,
            'parent_id' => $data['parent_id'] ?? $folder->parent_id,
        ]);

        return $folder->fresh();
    }

    /**
     * Delete folder and optionally its contents
     */
    public function deleteFolder(MediaFolder $folder, bool $deleteContents = false): bool
    {
        if ($deleteContents) {
            // Delete all files in folder and subfolders
            $descendants = $folder->getAllDescendants();
            foreach ($descendants as $descendant) {
                $this->bulkDelete($descendant->files()->pluck('id')->toArray(), true);
            }
        } else {
            // Move files to parent folder
            $folder->files()->update(['folder_id' => $folder->parent_id]);
            
            // Move subfolders to parent
            $folder->children()->update(['parent_id' => $folder->parent_id]);
        }

        return $folder->delete();
    }

    /**
     * Get folder tree structure
     */
    public function getFolderTree(?int $parentId = null): array
    {
        $folders = MediaFolder::where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->get();

        return $folders->map(function ($folder) {
            return [
                'id' => $folder->id,
                'name' => $folder->name,
                'files_count' => $folder->files_count,
                'total_files_count' => $folder->total_files_count,
                'children' => $this->getFolderTree($folder->id),
            ];
        })->toArray();
    }
}
