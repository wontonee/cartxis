<?php

namespace Cartxis\CMS\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Cartxis\CMS\Models\MediaFolder;
use Cartxis\CMS\Services\MediaService;

class FolderController extends Controller
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    /**
     * Create a new folder
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:media_folders,id',
        ]);

        $folder = $this->mediaService->createFolder(
            $request->input('name'),
            $request->input('parent_id')
        );

        return redirect()->back()->with('success', 'Folder created successfully');
    }

    /**
     * Update folder
     */
    public function update(Request $request, MediaFolder $folder): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:media_folders,id',
        ]);

        $this->mediaService->updateFolder($folder, $request->all());

        return redirect()->back()->with('success', 'Folder updated successfully');
    }

    /**
     * Delete folder
     */
    public function destroy(Request $request, MediaFolder $folder): RedirectResponse
    {
        $request->validate([
            'delete_contents' => 'boolean',
        ]);

        $deleteContents = $request->input('delete_contents', false);
        $this->mediaService->deleteFolder($folder, $deleteContents);

        return redirect()->back()->with('success', 'Folder deleted successfully');
    }
}
