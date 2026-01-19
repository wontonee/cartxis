<?php

namespace Cartxis\Marketing\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Cartxis\Marketing\Models\Promotion;
use Cartxis\Marketing\Services\PromotionService;
use Cartxis\Marketing\Http\Requests\StorePromotionRequest;
use Cartxis\Marketing\Http\Requests\UpdatePromotionRequest;

class PromotionController extends Controller
{
    public function __construct(
        protected PromotionService $promotionService
    ) {}

    /**
     * Display a listing of promotions.
     */
    public function index(Request $request): Response
    {
        $query = Promotion::query()->with('creator');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'priority');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $promotions = $query->paginate($request->get('per_page', 15))->withQueryString();

        // Get stats
        $stats = [
            'total' => Promotion::count(),
            'active' => Promotion::where('is_active', true)->count(),
            'usage_count' => Promotion::sum('usage_count'),
            'revenue_generated' => Promotion::sum('total_revenue_generated'),
        ];

        return Inertia::render('Admin/Marketing/Promotions/Index', [
            'promotions' => $promotions,
            'filters' => $request->only(['search', 'status', 'type', 'sort_by', 'sort_order']),
            'stats' => $stats,
            'promotionTypes' => [
                Promotion::TYPE_CATALOG_RULE => 'Catalog Price Rule',
                Promotion::TYPE_CART_RULE => 'Cart Price Rule',
                Promotion::TYPE_BUNDLE => 'Bundle Deal',
                Promotion::TYPE_FLASH_SALE => 'Flash Sale',
                Promotion::TYPE_TIERED_PRICING => 'Tiered Pricing',
            ],
        ]);
    }

    /**
     * Show the form for creating a new promotion.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Marketing/Promotions/Create', [
            'promotionTypes' => [
                Promotion::TYPE_CATALOG_RULE => 'Catalog Price Rule',
                Promotion::TYPE_CART_RULE => 'Cart Price Rule',
                Promotion::TYPE_BUNDLE => 'Bundle Deal',
                Promotion::TYPE_FLASH_SALE => 'Flash Sale',
                Promotion::TYPE_TIERED_PRICING => 'Tiered Pricing',
            ],
            'badgePositions' => [
                Promotion::BADGE_TOP_LEFT => 'Top Left',
                Promotion::BADGE_TOP_RIGHT => 'Top Right',
                Promotion::BADGE_BOTTOM_LEFT => 'Bottom Left',
                Promotion::BADGE_BOTTOM_RIGHT => 'Bottom Right',
            ],
        ]);
    }

    /**
     * Store a newly created promotion.
     */
    public function store(StorePromotionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();

        Promotion::create($validated);

        return redirect()
            ->route('admin.marketing.promotions.index')
            ->with('success', 'Promotion created successfully.');
    }

    /**
     * Display the specified promotion.
     */
    public function show(Promotion $promotion): Response
    {
        $promotion->load(['creator', 'products']);

        $analytics = $this->promotionService->getAnalytics($promotion->id);

        return Inertia::render('Admin/Marketing/Promotions/Show', [
            'promotion' => $promotion,
            'analytics' => $analytics,
        ]);
    }

    /**
     * Show the form for editing the specified promotion.
     */
    public function edit(Promotion $promotion): Response
    {
        $promotion->load('products');

        return Inertia::render('Admin/Marketing/Promotions/Edit', [
            'promotion' => $promotion,
            'promotionTypes' => [
                Promotion::TYPE_CATALOG_RULE => 'Catalog Price Rule',
                Promotion::TYPE_CART_RULE => 'Cart Price Rule',
                Promotion::TYPE_BUNDLE => 'Bundle Deal',
                Promotion::TYPE_FLASH_SALE => 'Flash Sale',
                Promotion::TYPE_TIERED_PRICING => 'Tiered Pricing',
            ],
            'badgePositions' => [
                Promotion::BADGE_TOP_LEFT => 'Top Left',
                Promotion::BADGE_TOP_RIGHT => 'Top Right',
                Promotion::BADGE_BOTTOM_LEFT => 'Bottom Left',
                Promotion::BADGE_BOTTOM_RIGHT => 'Bottom Right',
            ],
        ]);
    }

    /**
     * Update the specified promotion.
     */
    public function update(UpdatePromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $validated = $request->validated();

        $promotion->update($validated);

        return redirect()
            ->route('admin.marketing.promotions.index')
            ->with('success', 'Promotion updated successfully.');
    }

    /**
     * Remove the specified promotion.
     */
    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->delete();

        return redirect()
            ->route('admin.marketing.promotions.index')
            ->with('success', 'Promotion deleted successfully.');
    }

    /**
     * Bulk activate promotions.
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:promotions,id',
        ]);

        $count = $this->promotionService->bulkActivate($request->ids);

        return response()->json([
            'message' => "{$count} promotion(s) activated successfully.",
        ]);
    }

    /**
     * Bulk deactivate promotions.
     */
    public function bulkDeactivate(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:promotions,id',
        ]);

        $count = $this->promotionService->bulkDeactivate($request->ids);

        return response()->json([
            'message' => "{$count} promotion(s) deactivated successfully.",
        ]);
    }

    /**
     * Bulk delete promotions.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:promotions,id',
        ]);

        $count = $this->promotionService->bulkDelete($request->ids);

        return response()->json([
            'message' => "{$count} promotion(s) deleted successfully.",
        ]);
    }

    /**
     * Get promotion analytics.
     */
    public function analytics(Promotion $promotion): JsonResponse
    {
        $analytics = $this->promotionService->getAnalytics($promotion->id);

        return response()->json($analytics);
    }
}
