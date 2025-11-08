<?php

namespace Vortex\Marketing\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Vortex\Marketing\Models\Coupon;
use Vortex\Marketing\Services\CouponService;
use Vortex\Marketing\Http\Requests\StoreCouponRequest;
use Vortex\Marketing\Http\Requests\UpdateCouponRequest;

class CouponController extends Controller
{
    public function __construct(
        protected CouponService $couponService
    ) {}

    /**
     * Display a listing of coupons.
     */
    public function index(Request $request): Response
    {
        $query = Coupon::query();

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'expired') {
                $query->where('end_date', '<', now());
            }
        }

        // Filter by type
        if ($type = $request->get('type')) {
            if ($type !== 'all') {
                $query->where('type', $type);
            }
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $coupons = $query->paginate($request->get('per_page', 15))->withQueryString();

        // Get stats
        $stats = [
            'total' => Coupon::count(),
            'active' => Coupon::where('is_active', true)->count(),
            'used_today' => \Vortex\Marketing\Models\CouponUsage::whereDate('used_at', today())->count(),
            'total_discount' => \Vortex\Marketing\Models\CouponUsage::sum('discount_amount'),
        ];

        return Inertia::render('Admin/Marketing/Coupons/Index', [
            'coupons' => $coupons,
            'filters' => $request->only(['search', 'status', 'type']),
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new coupon.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Marketing/Coupons/Create', [
            'couponTypes' => [
                Coupon::TYPE_PERCENTAGE => 'Percentage',
                Coupon::TYPE_FIXED_AMOUNT => 'Fixed Amount',
                Coupon::TYPE_FREE_SHIPPING => 'Free Shipping',
                Coupon::TYPE_BUY_X_GET_Y => 'Buy X Get Y',
                Coupon::TYPE_FIXED_PRICE => 'Fixed Price',
            ],
        ]);
    }

    /**
     * Store a newly created coupon.
     */
    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['code'] = strtoupper($validated['code']);
        $validated['created_by'] = auth()->id();

        Coupon::create($validated);

        return redirect()
            ->route('admin.marketing.coupons.index')
            ->with('success', 'Coupon created successfully.');
    }

    /**
     * Display the specified coupon.
     */
    public function show(Coupon $coupon): Response
    {
        $coupon->load(['creator', 'usages.customer', 'usages.order']);

        $analytics = $this->couponService->getAnalytics($coupon->id);

        return Inertia::render('Admin/Marketing/Coupons/Show', [
            'coupon' => $coupon,
            'analytics' => $analytics,
        ]);
    }

    /**
     * Show the form for editing the specified coupon.
     */
    public function edit(Coupon $coupon): Response
    {
        $analyticsData = $this->couponService->getAnalytics($coupon->id);
        
        // Get recent coupon usages for the sidebar
        $recentUses = \Vortex\Marketing\Models\CouponUsage::where('coupon_id', $coupon->id)
            ->with('customer', 'order')
            ->orderBy('used_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($usage) {
                return [
                    'id' => $usage->id,
                    'customer_name' => $usage->customer ? $usage->customer->name : 'Guest',
                    'order_id' => $usage->order_id,
                    'discount_amount' => $usage->discount_amount,
                    'order_total' => $usage->order_subtotal + $usage->discount_amount,
                    'used_at' => $usage->used_at->toISOString(),
                ];
            });

        $analytics = [
            'total_uses' => $analyticsData['total_uses'],
            'unique_customers' => $analyticsData['unique_customers'],
            'total_discount' => $analyticsData['total_discount_given'] ?? 0,
            'total_revenue' => 0, // Can be calculated if needed
            'avg_order_value' => $analyticsData['average_order_value'] ?? 0,
            'recent_uses' => $recentUses,
        ];

        return Inertia::render('Admin/Marketing/Coupons/Edit', [
            'coupon' => $coupon,
            'analytics' => $analytics,
        ]);
    }

    /**
     * Update the specified coupon.
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $validated = $request->validated();
        $validated['code'] = strtoupper($validated['code']);

        $coupon->update($validated);

        return redirect()
            ->route('admin.marketing.coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    /**
     * Remove the specified coupon.
     */
    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()
            ->route('admin.marketing.coupons.index')
            ->with('success', 'Coupon deleted successfully.');
    }

    /**
     * Bulk activate coupons.
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:coupons,id',
        ]);

        $count = $this->couponService->bulkActivate($request->ids);

        return response()->json([
            'message' => "{$count} coupon(s) activated successfully.",
        ]);
    }

    /**
     * Bulk deactivate coupons.
     */
    public function bulkDeactivate(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:coupons,id',
        ]);

        $count = $this->couponService->bulkDeactivate($request->ids);

        return response()->json([
            'message' => "{$count} coupon(s) deactivated successfully.",
        ]);
    }

    /**
     * Bulk delete coupons.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:coupons,id',
        ]);

        $count = $this->couponService->bulkDelete($request->ids);

        return response()->json([
            'message' => "{$count} coupon(s) deleted successfully.",
        ]);
    }

    /**
     * Get coupon analytics.
     */
    public function analytics(Coupon $coupon): JsonResponse
    {
        $analytics = $this->couponService->getAnalytics($coupon->id);

        return response()->json($analytics);
    }

    // ==================== Frontend API Methods ====================

    /**
     * Apply coupon to cart (Frontend).
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'cart_total' => 'required|numeric|min:0',
        ]);

        $customerId = auth()->user()?->customer?->id;
        $cartTotal = $request->cart_total;
        $cartItems = collect(session()->get('cart', [])); // Adjust based on your cart structure

        // Validate coupon
        $validation = $this->couponService->validate(
            $request->code,
            $customerId,
            $cartTotal,
            $cartItems
        );

        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['message'],
            ], 422);
        }

        // Apply coupon
        $coupon = $validation['coupon'];
        $result = $this->couponService->apply($coupon, $cartTotal, $cartItems);

        // Store in session
        session()->put('applied_coupon', $result);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'discount' => $result['discount_amount'],
            'coupon' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
            ],
        ]);
    }

    /**
     * Remove coupon from cart (Frontend).
     */
    public function remove(): JsonResponse
    {
        session()->forget('applied_coupon');

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully.',
        ]);
    }

    /**
     * Validate coupon without applying (Frontend).
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'cart_total' => 'required|numeric|min:0',
        ]);

        $customerId = auth()->user()?->customer?->id;
        $cartTotal = $request->cart_total;
        $cartItems = collect(session()->get('cart', []));

        $validation = $this->couponService->validate(
            $request->code,
            $customerId,
            $cartTotal,
            $cartItems
        );

        return response()->json($validation);
    }

    /**
     * Get list of available public coupons (Frontend).
     */
    public function listPublic(): JsonResponse
    {
        $coupons = $this->couponService->getPublicCoupons();

        return response()->json([
            'coupons' => $coupons->map(function ($coupon) {
                return [
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'description' => $coupon->description,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'min_order_amount' => $coupon->min_order_amount,
                    'end_date' => $coupon->end_date?->format('Y-m-d'),
                ];
            }),
        ]);
    }
}
