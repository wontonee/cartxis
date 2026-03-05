<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\CMS\Models\Page;
use Cartxis\CMS\Http\Resources\PageResource;
use Cartxis\CMS\Services\PageService;
use Cartxis\UIEditor\Models\PageLayout;
use Cartxis\UIEditor\Services\BlockRegistry;
use Cartxis\UIEditor\Services\LayoutService;

class EditorController extends Controller
{
    public function __construct(
        protected LayoutService $layoutService,
        protected BlockRegistry $blockRegistry,
        protected PageService $pageService
    ) {}

    /**
     * Open the full-screen visual editor for a CMS page.
     * If the page is the homepage (is_homepage = true) it uses the homepage layout slot.
     */
    public function show(Page $page): Response
    {
        $isHomepage = (bool) $page->is_homepage;

        $layout = $isHomepage
            ? $this->layoutService->getHomepage()
            : $this->layoutService->getForPage($page);

        $pageType = $isHomepage ? PageLayout::TYPE_HOMEPAGE : PageLayout::TYPE_CMS_PAGE;

        // For the homepage we reuse the dedicated save/publish routes (no page {id} required).
        // For regular pages we use the per-page routes.
        $saveUrl      = $isHomepage ? route('admin.uieditor.homepage.save')      : route('admin.uieditor.pages.save',    $page);
        $publishUrl   = $isHomepage ? route('admin.uieditor.homepage.publish')   : route('admin.uieditor.pages.publish',  $page);
        $unpublishUrl = $isHomepage ? route('admin.uieditor.homepage.unpublish') : route('admin.uieditor.pages.unpublish', $page);
        $previewUrl   = $isHomepage ? route('admin.uieditor.homepage.preview')   : route('admin.uieditor.pages.preview',  $page);

        return Inertia::render('Admin/UIEditor/Editor', [
            'page'         => [
                'id'            => $page->id,
                'title'         => $page->title,
                'url'           => $page->url_key,
                'url_key'       => $page->url_key,
                'status'        => $page->status,
                'is_homepage'   => $isHomepage,
                'meta_title'    => $page->meta_title,
                'meta_description' => $page->meta_description,
                'meta_keywords' => $page->meta_keywords,
            ],
            'pageType'     => $pageType,
            'layoutData'   => $layout?->layout_data ?? $this->layoutService->emptyLayout(),
            'layoutStatus' => $layout?->status,
            'publishedAt'  => $layout?->published_at?->toIso8601String(),
            'blockTypes'   => array_values($this->blockRegistry->all()),
            'saveUrl'      => $saveUrl,
            'publishUrl'   => $publishUrl,
            'unpublishUrl' => $unpublishUrl,
            'previewUrl'   => $previewUrl,
        ]);
    }

    /**
     * Save the current layout as a draft (never auto-publishes).
     */
    public function save(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'layout_data'          => 'required|array',
            'layout_data.version'  => 'required|string',
            'layout_data.sections' => 'present|array',
        ]);

        $layout = $this->layoutService->saveDraft(
            $validated['layout_data'],
            PageLayout::TYPE_CMS_PAGE,
            $page->id
        );

        return response()->json([
            'status'  => $layout->status,
            'savedAt' => now()->toIso8601String(),
            'message' => 'Draft saved successfully.',
        ]);
    }

    /**
     * Publish the layout, making it live on the storefront.
     */
    public function publish(Page $page): JsonResponse
    {
        $layout = $this->layoutService->getForPage($page);

        if (! $layout) {
            return response()->json(['message' => 'No layout found to publish.'], 422);
        }

        $layout = $this->layoutService->publish($layout);

        return response()->json([
            'status'      => $layout->status,
            'publishedAt' => $layout->published_at->toIso8601String(),
            'message'     => 'Layout published successfully.',
        ]);
    }

    /**
     * Revert a published layout back to draft (takes the page offline).
     */
    public function unpublish(Page $page): JsonResponse
    {
        $layout = $this->layoutService->getForPage($page);

        if (! $layout) {
            return response()->json(['message' => 'No layout found.'], 422);
        }

        $layout = $this->layoutService->unpublish($layout);

        return response()->json([
            'status'  => $layout->status,
            'message' => 'Layout unpublished. The page will now render its raw HTML content.',
        ]);
    }

    /**
     * Update only the page meta/settings (title, url_key, status, SEO).
     * Separated from the layout save to keep concerns clean.
     */
    public function updatePageSettings(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'url_key'          => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('pages', 'url_key')->ignore($page->id)],
            'status'           => 'required|in:draft,published,disabled',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:500',
        ]);

        // Homepage status is always published — prevent override
        if ($page->is_homepage) {
            unset($validated['status']);
        }

        $this->pageService->update($page, $validated);

        $page->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Page settings saved.',
            'page'    => [
                'id'               => $page->id,
                'title'            => $page->title,
                'url'              => $page->url_key,
                'url_key'          => $page->url_key,
                'status'           => $page->status,
                'is_homepage'      => (bool) $page->is_homepage,
                'meta_title'       => $page->meta_title,
                'meta_description' => $page->meta_description,
                'meta_keywords'    => $page->meta_keywords,
            ],
        ]);
    }

    /**
     * Render a bare-theme preview of the current draft layout (used inside iframe).
     */
    public function preview(Page $page): Response
    {
        $layout = $this->layoutService->getForPage($page);

        return Inertia::render('Admin/UIEditor/Preview', [
            'page'       => ['id' => $page->id, 'title' => $page->title],
            'layoutData' => $layout?->layout_data ?? $this->layoutService->emptyLayout(),
        ]);
    }
}
