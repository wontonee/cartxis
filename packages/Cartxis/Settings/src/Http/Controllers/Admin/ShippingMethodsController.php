<?php

namespace Cartxis\Settings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cartxis\Core\Services\SettingService;
use Cartxis\Core\Models\ShippingMethod;
use Cartxis\Core\Models\ShippingRate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShippingMethodsController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Display a listing of shipping methods.
     */
    public function index()
    {
        $methods = ShippingMethod::with('rates')
            ->ordered()
            ->paginate(15);

        return Inertia::render('Admin/Settings/ShippingMethods/Index', [
            'methods' => $methods,
            'extensions' => [
                'delivery' => [
                    'enabled' => (bool) $this->settingService->get('shipping.delivery.enabled', false),
                ],
                'shiprocket' => [
                    'enabled' => (bool) $this->settingService->get('shipping.shiprocket.enabled', false),
                ],
            ],
        ]);
    }

    /**
     * Toggle shipment extension status.
     */
    public function toggleExtension(Request $request, string $extension)
    {
        if (!in_array($extension, ['shiprocket', 'delivery'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported shipment extension.',
            ], 422);
        }

        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        if ($extension === 'shiprocket') {
            $this->settingService->set('shipping.shiprocket.enabled', (bool) $validated['enabled'], 'boolean', 'shipping');
        }

        if ($extension === 'delivery') {
            $this->settingService->set('shipping.delivery.enabled', (bool) $validated['enabled'], 'boolean', 'shipping');
        }

        return response()->json([
            'success' => true,
            'enabled' => (bool) $validated['enabled'],
            'message' => 'Shipment extension updated successfully.',
        ]);
    }

    /**
     * Show the form for creating a new shipping method.
     */
    public function create()
    {
        return Inertia::render('Admin/Settings/ShippingMethods/Create', [
            'types' => [
                ['id' => 'flat-rate', 'name' => 'Flat Rate'],
                ['id' => 'calculated', 'name' => 'Calculated (Weight-based)'],
            ],
        ]);
    }

    /**
     * Store a newly created shipping method.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:shipping_methods|max:255',
            'slug' => 'required|string|unique:shipping_methods|max:255',
            'type' => 'required|in:flat-rate,calculated',
            'base_cost' => 'required|numeric|min:0|max:99999.99',
            'cost_per_kg' => 'required|numeric|min:0|max:99999.9999',
            'description' => 'nullable|string|max:500',
            'is_default' => 'boolean',
        ]);

        $method = ShippingMethod::create($validated);

        if ($validated['is_default'] ?? false) {
            $method->setAsDefault();
        }

        return redirect()
            ->route('admin.settings.shipping-methods.edit', $method)
            ->with('success', 'Shipping method created successfully.');
    }

    /**
     * Display the specified shipping method.
     */
    public function show(ShippingMethod $shippingMethod)
    {
        $method = $shippingMethod->load('rates');

        return Inertia::render('Admin/Settings/ShippingMethods/Show', [
            'method' => $method,
            'types' => [
                ['id' => 'flat-rate', 'name' => 'Flat Rate'],
                ['id' => 'calculated', 'name' => 'Calculated (Weight-based)'],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified shipping method.
     */
    public function edit(ShippingMethod $shippingMethod)
    {
        $method = $shippingMethod->load('rates');

        return Inertia::render('Admin/Settings/ShippingMethods/Edit', [
            'method' => $method,
            'types' => [
                ['id' => 'flat-rate', 'name' => 'Flat Rate'],
                ['id' => 'calculated', 'name' => 'Calculated (Weight-based)'],
            ],
        ]);
    }

    /**
     * Update the specified shipping method.
     */
    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:shipping_methods,name,' . $shippingMethod->id . '|max:255',
            'slug' => 'required|string|unique:shipping_methods,slug,' . $shippingMethod->id . '|max:255',
            'type' => 'required|in:flat-rate,calculated',
            'base_cost' => 'required|numeric|min:0|max:99999.99',
            'cost_per_kg' => 'required|numeric|min:0|max:99999.9999',
            'description' => 'nullable|string|max:500',
            'is_default' => 'boolean',
        ]);

        $shippingMethod->update($validated);

        if ($validated['is_default'] ?? false) {
            $shippingMethod->setAsDefault();
        }

        return redirect()
            ->route('admin.settings.shipping-methods.edit', $shippingMethod)
            ->with('success', 'Shipping method updated successfully.');
    }

    /**
     * Delete the specified shipping method.
     */
    public function destroy(ShippingMethod $shippingMethod)
    {
        if ($shippingMethod->is_default) {
            return back()->with('error', 'Cannot delete the default shipping method.');
        }

        $shippingMethod->rates()->delete();
        $shippingMethod->delete();

        return redirect()
            ->route('admin.settings.shipping-methods.index')
            ->with('success', 'Shipping method deleted successfully.');
    }

    /**
     * Set the specified method as default.
     */
    public function setDefault(ShippingMethod $shippingMethod)
    {
        $shippingMethod->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => 'Default shipping method updated.',
        ]);
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(ShippingMethod $shippingMethod)
    {
        $shippingMethod->toggleStatus();

        return response()->json([
            'success' => true,
            'status' => $shippingMethod->status,
            'message' => 'Status updated successfully.',
        ]);
    }

    /**
     * Add a new shipping rate to a method.
     */
    public function addRate(Request $request, ShippingMethod $shippingMethod)
    {
        $validated = $request->validate([
            'country' => 'required|string|size:2',
            'state' => 'nullable|string|size:2',
            'min_weight' => 'required|numeric|min:0',
            'max_weight' => 'required|numeric|gt:min_weight',
            'base_cost' => 'required|numeric|min:0|max:99999.99',
            'cost_per_kg' => 'required|numeric|min:0|max:99999.9999',
            'status' => 'required|in:active,inactive',
        ]);

        $rate = $shippingMethod->rates()->create($validated);

        return response()->json([
            'success' => true,
            'rate' => $rate,
            'message' => 'Shipping rate added successfully.',
        ]);
    }

    /**
     * Update a shipping rate.
     */
    public function updateRate(Request $request, ShippingRate $shippingRate)
    {
        $validated = $request->validate([
            'country' => 'required|string|size:2',
            'state' => 'nullable|string|size:2',
            'min_weight' => 'required|numeric|min:0',
            'max_weight' => 'required|numeric|gt:min_weight',
            'base_cost' => 'required|numeric|min:0|max:99999.99',
            'cost_per_kg' => 'required|numeric|min:0|max:99999.9999',
            'status' => 'required|in:active,inactive',
        ]);

        $shippingRate->update($validated);

        return response()->json([
            'success' => true,
            'rate' => $shippingRate,
            'message' => 'Shipping rate updated successfully.',
        ]);
    }

    /**
     * Delete a shipping rate.
     */
    public function deleteRate(ShippingRate $shippingRate)
    {
        $shippingRate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipping rate deleted successfully.',
        ]);
    }

    /**
     * Toggle rate status.
     */
    public function toggleRateStatus(ShippingRate $shippingRate)
    {
        $shippingRate->toggleStatus();

        return response()->json([
            'success' => true,
            'status' => $shippingRate->status,
            'message' => 'Rate status updated.',
        ]);
    }

    /**
     * Calculate shipping cost for given parameters.
     */
    public function calculateCost(Request $request, ShippingMethod $shippingMethod)
    {
        $validated = $request->validate([
            'weight' => 'required|numeric|min:0',
            'country' => 'nullable|string|size:2',
            'state' => 'nullable|string|size:2',
        ]);

        $cost = $shippingMethod->calculateCost(
            $validated['weight'],
            $validated['country'] ?? null,
            $validated['state'] ?? null
        );

        return response()->json([
            'success' => true,
            'cost' => $cost,
            'currency' => 'USD',
        ]);
    }
}
