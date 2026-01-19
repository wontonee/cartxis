<?php

namespace Cartxis\CMS\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\CMS\Http\Resources\MediaFileResource;
use Cartxis\CMS\Models\MediaFile;
use Cartxis\CMS\Repositories\MediaRepository;
use Cartxis\CMS\Services\MediaService;

class MediaController extends Controller
{
    public function __construct(
        protected MediaService $mediaService,
        protected MediaRepository $mediaRepository
    ) {}

    /**
     * Display media library
     */
    public function index(Request $request): Response
    {
        $filters = [
            'folder_id' => $request->query('folder_id'),
            'type' => $request->query('type'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
        ];

        $media = $this->mediaRepository->paginate($filters, $request->query('per_page', 24));
        $folders = $this->mediaRepository->getRootFolders();
        $statistics = $this->mediaRepository->getStatistics();

        // Get current folder if viewing a folder
        $currentFolder = null;
        if ($filters['folder_id']) {
            $currentFolder = $this->mediaRepository->findFolder($filters['folder_id']);
        }

        return Inertia::render('Admin/Content/Media/Index', [
            'media' => MediaFileResource::collection($media),
            'folders' => $folders,
            'currentFolder' => $currentFolder,
            'statistics' => $statistics,
            'filters' => $filters,
        ]);
    }

    /**
     * Upload files
     */
    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|max:' . config('cms.media.max_file_size', 10240),
            'folder_id' => 'nullable|exists:media_folders,id',
            'alt_text' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $uploaded = [];
        $folderId = $request->input('folder_id');
        $metadata = [
            'alt_text' => $request->input('alt_text'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
        ];

        foreach ($request->file('files') as $file) {
            try {
                $mediaFile = $this->mediaService->upload($file, $folderId, $metadata);
                $uploaded[] = new MediaFileResource($mediaFile);
            } catch (\Exception $e) {
                \Log::error('File upload failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', count($uploaded) . ' file(s) uploaded successfully');
    }

    /**
     * Show file details
     */
    public function show(MediaFile $media): JsonResponse
    {
        $media->load(['folder', 'creator', 'updater', 'usages.usable']);
        
        return response()->json([
            'file' => new MediaFileResource($media),
        ]);
    }

    /**
     * Update file metadata
     */
    public function update(Request $request, MediaFile $media): RedirectResponse
    {
        $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'folder_id' => 'nullable|exists:media_folders,id',
        ]);

        $this->mediaService->update($media, $request->all());

        return redirect()->back()->with('success', 'Media file updated successfully');
    }

    /**
     * Delete file
     */
    public function destroy(MediaFile $media): RedirectResponse
    {
        if ($media->used_count > 0) {
            return redirect()->back()->with('error', 'Cannot delete file that is currently in use');
        }

        $this->mediaService->delete($media, true);

        return redirect()->back()->with('success', 'Media file deleted successfully');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete,move',
            'ids' => 'required|array',
            'ids.*' => 'exists:media_files,id',
            'folder_id' => 'nullable|exists:media_folders,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');

        match ($action) {
            'delete' => $this->mediaService->bulkDelete($ids, true),
            'move' => $this->mediaService->moveToFolder($ids, $request->input('folder_id')),
        };

        return redirect()->back()->with('success', ucfirst($action) . ' completed successfully');
    }

    /**
     * Get files for media picker
     */
    public function picker(Request $request): JsonResponse
    {
        $filters = [
            'type' => $request->query('type', 'images'),
            'search' => $request->query('search'),
            'folder_id' => $request->query('folder_id'),
        ];

        $media = $this->mediaRepository->paginate($filters, $request->query('per_page', 20));

        return response()->json([
            'media' => MediaFileResource::collection($media),
        ]);
    }
}
