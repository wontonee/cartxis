<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\UIEditor\Models\PageLayout;
use Cartxis\UIEditor\Services\BlockRegistry;
use Cartxis\UIEditor\Services\LayoutService;

class HomepageEditorController extends Controller
{
    public function __construct(
        protected LayoutService $layoutService,
        protected BlockRegistry $blockRegistry
    ) {}

    /**
     * Open the full-screen visual editor for the homepage.
     */
    public function show(): Response
    {
        $layout = $this->layoutService->getHomepage();

        return Inertia::render('Admin/UIEditor/Editor', [
            'page'         => [
                'id'     => null,
                'title'  => 'Homepage',
                'url'    => '/',
                'status' => 'published',
            ],
            'pageType'     => PageLayout::TYPE_HOMEPAGE,
            'layoutData'   => $layout?->layout_data ?? $this->layoutService->emptyLayout(),
            'layoutStatus' => $layout?->status,
            'publishedAt'  => $layout?->published_at?->toIso8601String(),
            'blockTypes'   => array_values($this->blockRegistry->all()),
            'saveUrl'      => route('admin.uieditor.homepage.save'),
            'publishUrl'   => route('admin.uieditor.homepage.publish'),
            'unpublishUrl' => route('admin.uieditor.homepage.unpublish'),
            'previewUrl'   => route('admin.uieditor.homepage.preview'),
        ]);
    }

    /**
     * Save the homepage layout as a draft.
     */
    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'layout_data'          => 'required|array',
            'layout_data.version'  => 'required|string',
            'layout_data.sections' => 'present|array',
        ]);

        $layout = $this->layoutService->saveDraft(
            $validated['layout_data'],
            PageLayout::TYPE_HOMEPAGE
        );

        return response()->json([
            'status'  => $layout->status,
            'savedAt' => now()->toIso8601String(),
            'message' => 'Draft saved successfully.',
        ]);
    }

    /**
     * Publish the homepage layout.
     */
    public function publish(): JsonResponse
    {
        $layout = $this->layoutService->getHomepage();

        if (! $layout) {
            return response()->json(['message' => 'No homepage layout found to publish.'], 422);
        }

        $layout = $this->layoutService->publish($layout);

        return response()->json([
            'status'      => $layout->status,
            'publishedAt' => $layout->published_at->toIso8601String(),
            'message'     => 'Homepage layout published successfully.',
        ]);
    }

    /**
     * Revert the homepage layout to draft.
     */
    public function unpublish(): JsonResponse
    {
        $layout = $this->layoutService->getHomepage();

        if (! $layout) {
            return response()->json(['message' => 'No homepage layout found.'], 422);
        }

        $layout = $this->layoutService->unpublish($layout);

        return response()->json([
            'status'  => $layout->status,
            'message' => 'Homepage layout unpublished.',
        ]);
    }

    /**
     * Iframe preview of the homepage draft layout.
     */
    public function preview(): Response
    {
        $layout = $this->layoutService->getHomepage();

        return Inertia::render('Admin/UIEditor/Preview', [
            'page'       => ['id' => null, 'title' => 'Homepage'],
            'layoutData' => $layout?->layout_data ?? $this->layoutService->emptyLayout(),
        ]);
    }
}
