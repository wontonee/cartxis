<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\UIEditor\Models\GlobalRegion;
use Cartxis\UIEditor\Services\BlockRegistry;
use Cartxis\UIEditor\Services\LayoutService;

class GlobalRegionController extends Controller
{
    public function __construct(
        protected BlockRegistry $blockRegistry,
        protected LayoutService $layoutService,
    ) {}

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(): Response
    {
        $regions = GlobalRegion::orderBy('region_type')
            ->orderBy('name')
            ->get()
            ->map(fn (GlobalRegion $r) => [
                'id'          => $r->id,
                'name'        => $r->name,
                'slug'        => $r->slug,
                'description' => $r->description,
                'region_type' => $r->region_type,
                'status'      => $r->status,
                'published_at'=> $r->published_at?->toIso8601String(),
                'updated_at'  => $r->updated_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/UIEditor/Regions/Index', [
            'regions' => $regions,
        ]);
    }

    // ─── Store (create + redirect to editor) ─────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'description' => 'nullable|string|max:250',
            'region_type' => 'required|in:header,footer,section,banner,sidebar',
        ]);

        $data['slug']        = Str::slug($data['name']);
        $data['layout_data'] = $this->layoutService->emptyLayout();

        // Ensure unique slug
        $base  = $data['slug'];
        $count = 1;
        while (GlobalRegion::where('slug', $data['slug'])->exists()) {
            $data['slug'] = "{$base}-{$count}";
            $count++;
        }

        $region = GlobalRegion::create($data);

        return redirect()->route('admin.uieditor.regions.editor', $region);
    }

    // ─── Editor (open UIEditor for this region) ───────────────────────────────

    public function editor(GlobalRegion $region): Response
    {
        return Inertia::render('Admin/UIEditor/Editor', [
            'page'         => null,          // no CMS page association
            'pageType'     => 'global_region',
            'regionMeta'   => [
                'id'          => $region->id,
                'name'        => $region->name,
                'slug'        => $region->slug,
                'description' => $region->description,
                'region_type' => $region->region_type,
                'status'      => $region->status,
            ],
            'layoutData'   => $region->layout_data   ?? $this->layoutService->emptyLayout(),
            'layoutStatus' => $region->status,
            'publishedAt'  => $region->published_at?->toIso8601String(),
            'blockTypes'   => array_values($this->blockRegistry->all()),
            'saveUrl'      => route('admin.uieditor.regions.save',      $region),
            'publishUrl'   => route('admin.uieditor.regions.publish',   $region),
            'unpublishUrl' => route('admin.uieditor.regions.unpublish', $region),
            'previewUrl'   => route('admin.uieditor.regions.preview',   $region),
        ]);
    }

    // ─── Save (draft) ────────────────────────────────────────────────────────

    public function save(Request $request, GlobalRegion $region): JsonResponse
    {
        $validated = $request->validate([
            'layout_data'          => 'required|array',
            'layout_data.version'  => 'required|string',
            'layout_data.sections' => 'present|array',
        ]);

        $region->update([
            'layout_data' => $validated['layout_data'],
            'status'      => GlobalRegion::STATUS_DRAFT,
        ]);

        return response()->json([
            'status'  => $region->status,
            'savedAt' => now()->toIso8601String(),
            'message' => 'Region draft saved.',
        ]);
    }

    // ─── Publish ─────────────────────────────────────────────────────────────

    public function publish(GlobalRegion $region): JsonResponse
    {
        if (! $region->layout_data) {
            return response()->json(['message' => 'No layout to publish.'], 422);
        }

        $region->update([
            'status'                => GlobalRegion::STATUS_PUBLISHED,
            'published_at'          => now(),
            'published_layout_data' => $region->layout_data,
        ]);

        return response()->json([
            'status'      => $region->status,
            'publishedAt' => $region->published_at->toIso8601String(),
            'message'     => 'Region published. All pages using this region now show the updated version.',
        ]);
    }

    // ─── Unpublish ───────────────────────────────────────────────────────────

    public function unpublish(GlobalRegion $region): JsonResponse
    {
        $region->update([
            'status'                => GlobalRegion::STATUS_DRAFT,
            'published_at'          => null,
            'published_layout_data' => null,
        ]);

        return response()->json([
            'status'  => $region->status,
            'message' => 'Region unpublished.',
        ]);
    }

    // ─── Preview ─────────────────────────────────────────────────────────────

    public function preview(GlobalRegion $region)
    {
        return Inertia::render('Admin/UIEditor/Preview', [
            'layoutData' => $region->layout_data ?? $this->layoutService->emptyLayout(),
            'title'      => $region->name,
            'type'       => 'global_region',
        ]);
    }

    // ─── Update (name/description/type from Index modal) ─────────────────────

    public function update(Request $request, GlobalRegion $region): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:120',
            'description' => 'nullable|string|max:250',
            'region_type' => 'sometimes|in:header,footer,section,banner,sidebar',
        ]);

        $region->update($data);

        return response()->json(['message' => 'Region updated.', 'region' => $region->only('id', 'name', 'slug', 'description', 'region_type', 'status')]);
    }

    // ─── Destroy ─────────────────────────────────────────────────────────────

    public function destroy(GlobalRegion $region): JsonResponse
    {
        $region->delete();

        return response()->json(['message' => 'Region deleted.']);
    }

    // ─── Preview data (JSON; used by GlobalRegionBlock.vue in editor) ────────

    public function previewData(GlobalRegion $region): JsonResponse
    {
        return response()->json([
            'layout_data' => $region->layout_data ?? $this->layoutService->emptyLayout(),
            'status'      => $region->status,
        ]);
    }

    // ─── API: list published regions (used by BlockPalette in editor) ─────────

    public function listForEditor(): JsonResponse
    {
        $regions = GlobalRegion::orderBy('region_type')->orderBy('name')
            ->get()
            ->map(fn (GlobalRegion $r) => [
                'id'          => $r->id,
                'name'        => $r->name,
                'slug'        => $r->slug,
                'region_type' => $r->region_type,
                'status'      => $r->status,
            ]);

        return response()->json($regions);
    }
}
