<?php

declare(strict_types=1);

namespace Vortex\CMS\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Vortex\CMS\Http\Requests\PageRequest;
use Vortex\CMS\Http\Resources\PageResource;
use Vortex\CMS\Models\Page;
use Vortex\CMS\Services\PageService;
use Vortex\CMS\Repositories\PageRepository;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PagesController extends Controller
{
    public function __construct(
        protected PageService $pageService,
        protected PageRepository $pageRepository
    ) {}

    /**
     * Display a listing of pages.
     */
    public function index(Request $request): Response
    {
        $pages = $this->pageRepository->paginate(
            $request->only(['search', 'status', 'channel_id', 'sort_by', 'sort_order']),
            $request->input('per_page', 20)
        );

        $statistics = $this->pageRepository->getCountByStatus();

        return Inertia::render('Admin/Content/Pages/Index', [
            'pages' => PageResource::collection($pages),
            'statistics' => $statistics,
            'filters' => $request->only(['search', 'status', 'sort_by', 'sort_order', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new page.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Content/Pages/Create');
    }

    /**
     * Store a newly created page in storage.
     */
    public function store(PageRequest $request): RedirectResponse
    {
        try {
            $page = $this->pageService->create($request->validated());

            return redirect()
                ->route('admin.content.pages.index')
                ->with('success', 'Page created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create page: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(Page $page): Response
    {
        $page->load(['creator', 'updater']);
        
        return Inertia::render('Admin/Content/Pages/Edit', [
            'page' => PageResource::make($page)->resolve(),
        ]);
    }

    /**
     * Update the specified page in storage.
     */
    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        try {
            $this->pageService->update($page, $request->validated());

            return redirect()
                ->route('admin.content.pages.index')
                ->with('success', 'Page updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update page: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified page from storage.
     */
    public function destroy(Page $page): RedirectResponse
    {
        try {
            $this->pageService->delete($page);

            return back()->with('success', 'Page deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete page: ' . $e->getMessage());
        }
    }

    /**
     * Handle bulk actions (enable, disable, delete).
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:enable,disable,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:pages,id',
        ]);

        try {
            $count = match($validated['action']) {
                'enable' => $this->pageService->bulkUpdateStatus($validated['ids'], 'published'),
                'disable' => $this->pageService->bulkUpdateStatus($validated['ids'], 'disabled'),
                'delete' => $this->pageService->bulkDelete($validated['ids']),
            };

            $action = ucfirst($validated['action']);
            return back()->with('success', "{$action} action completed successfully on {$count} page(s).");
        } catch (\Exception $e) {
            return back()->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }

    /**
     * Preview page on storefront.
     */
    public function preview(Page $page): RedirectResponse
    {
        return redirect()->route('page.show', ['slug' => $page->url_key]);
    }

    /**
     * Check if URL key is available.
     */
    public function checkSlug(Request $request)
    {
        $slug = $request->input('slug');
        $excludeId = $request->input('exclude_id');

        // Return error if slug is empty
        if (empty($slug)) {
            return response()->json([
                'available' => false,
                'slug' => $slug,
                'error' => 'Slug cannot be empty',
            ], 400);
        }

        $exists = $this->pageService->slugExists($slug, $excludeId);

        return response()->json([
            'available' => !$exists,
            'slug' => $slug,
        ]);
    }
}
