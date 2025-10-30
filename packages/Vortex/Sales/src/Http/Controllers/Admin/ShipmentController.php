<?php

namespace Vortex\Sales\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Vortex\Sales\Models\Shipment;
use Vortex\Sales\Services\ShipmentService;
use Vortex\Shop\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ShipmentController extends Controller
{
    protected ShipmentService $shipmentService;

    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    /**
     * Display shipment listing
     */
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_order' => $request->input('sort_order', 'desc'),
            'per_page' => $request->input('per_page', 15),
        ];

        $shipments = $this->shipmentService->getShipments($filters);
        $statistics = $this->shipmentService->getStatistics();

        return Inertia::render('Admin/Sales/Shipments/Index', [
            'shipments' => $shipments,
            'filters' => $filters,
            'statistics' => $statistics,
            'statuses' => collect(Shipment::getStatuses())->map(function ($label, $value) {
                return ['value' => $value, 'label' => $label];
            })->values(),
        ]);
    }

    /**
     * Show create shipment form
     */
    public function create(Request $request): Response|RedirectResponse
    {
        $orderId = $request->input('order_id');
        $order = null;

        if ($orderId) {
            $order = Order::with(['items.product', 'user', 'shipments.shipmentItems'])
                ->findOrFail($orderId);

            if (!$order->canBeShipped()) {
                return redirect()
                    ->route('admin.sales.orders.show', $orderId)
                    ->with('error', 'This order cannot be shipped');
            }
        }

        return Inertia::render('Admin/Sales/Shipments/Create', [
            'order' => $order,
            'statuses' => collect(Shipment::getStatuses())->map(function ($label, $value) {
                return ['value' => $value, 'label' => $label];
            })->values(),
        ]);
    }

    /**
     * Store a new shipment
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'carrier' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|url|max:500',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $order = Order::findOrFail($validated['order_id']);
            $shipment = $this->shipmentService->createFromOrder($order, $validated);

            return redirect()
                ->route('admin.sales.shipments.show', $shipment->id)
                ->with('success', 'Shipment created successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display shipment details
     */
    public function show(int $id): Response
    {
        $shipment = Shipment::with([
            'order.user',
            'order.items.product',
            'shipmentItems.orderItem.product'
        ])->findOrFail($id);

        return Inertia::render('Admin/Sales/Shipments/Show', [
            'shipment' => $shipment,
            'statuses' => collect(Shipment::getStatuses())->map(function ($label, $value) {
                return ['value' => $value, 'label' => $label];
            })->values(),
        ]);
    }

    /**
     * Show edit shipment form
     */
    public function edit(int $id): Response|RedirectResponse
    {
        $shipment = Shipment::with([
            'order.user',
            'order.items.product',
            'order.shipments.shipmentItems',
            'shipmentItems.orderItem.product'
        ])->findOrFail($id);

        if (!$shipment->canEdit()) {
            return redirect()
                ->route('admin.sales.shipments.show', $id)
                ->with('error', 'This shipment cannot be edited');
        }

        return Inertia::render('Admin/Sales/Shipments/Edit', [
            'shipment' => $shipment,
            'statuses' => collect(Shipment::getStatuses())->map(function ($label, $value) {
                return ['value' => $value, 'label' => $label];
            })->values(),
        ]);
    }

    /**
     * Update shipment
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'carrier' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|url|max:500',
            'notes' => 'nullable|string|max:1000',
            'items' => 'sometimes|array|min:1',
            'items.*.order_item_id' => 'required_with:items|exists:order_items,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        try {
            $this->shipmentService->update($id, $validated);

            return redirect()
                ->route('admin.sales.shipments.show', $id)
                ->with('success', 'Shipment updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update tracking information
     */
    public function updateTracking(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'carrier' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|url|max:500',
        ]);

        try {
            $this->shipmentService->updateTracking($id, $validated);

            return back()->with('success', 'Tracking information updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark shipment as shipped
     */
    public function markAsShipped(int $id): RedirectResponse
    {
        try {
            $this->shipmentService->markAsShipped($id);

            return back()->with('success', 'Shipment marked as shipped');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update shipment status
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(Shipment::getStatuses())),
        ]);

        try {
            $this->shipmentService->updateStatus($id, $validated['status']);

            return back()->with('success', 'Shipment status updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel shipment
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $this->shipmentService->cancel($id, $validated['reason'] ?? null);

            return redirect()
                ->route('admin.sales.shipments.show', $id)
                ->with('success', 'Shipment cancelled successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Print shipping label (placeholder for future implementation)
     */
    public function printLabel(int $id)
    {
        $shipment = Shipment::with([
            'order.user',
            'order.billingAddress',
            'order.shippingAddress',
            'shipmentItems.orderItem.product'
        ])->findOrFail($id);

        // TODO: Implement shipping label generation with PDF
        // For now, return a simple view
        return view('sales::shipments.label', compact('shipment'));
    }
}
