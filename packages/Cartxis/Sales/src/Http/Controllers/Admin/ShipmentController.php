<?php

namespace Cartxis\Sales\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cartxis\Core\Services\SettingService;
use Cartxis\Sales\Models\Shipment;
use Cartxis\Sales\Services\ShiprocketService;
use Cartxis\Sales\Services\ShipmentService;
use Cartxis\Shop\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ShipmentController extends Controller
{
    protected ShipmentService $shipmentService;
    protected ShiprocketService $shiprocketService;
    protected SettingService $settingService;

    public function __construct(ShipmentService $shipmentService, ShiprocketService $shiprocketService, SettingService $settingService)
    {
        $this->shipmentService = $shipmentService;
        $this->shiprocketService = $shiprocketService;
        $this->settingService = $settingService;
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
     * Note: Shipments can only be created from orders
     */
    public function create(Request $request): Response|RedirectResponse
    {
        $orderId = $request->input('order_id');
        
        // Require order_id - shipments must be linked to orders
        if (!$orderId) {
            return redirect()
                ->route('admin.sales.orders.index')
                ->with('error', 'Please select an order first. Shipments must be created from an order.');
        }

        $order = Order::with(['items.product', 'user', 'shipments.shipmentItems'])
            ->findOrFail($orderId);

        if (!$order->canBeShipped()) {
            return redirect()
                ->route('admin.sales.orders.show', $orderId)
                ->with('error', 'This order cannot be shipped');
        }

        $shiprocketEnabled = (bool) $this->settingService->get('shipping.shiprocket.enabled', false);
        $shiprocketConfigured = $shiprocketEnabled
            && (string) $this->settingService->get('shipping.shiprocket.email', '') !== ''
            && (string) $this->settingService->get('shipping.shiprocket.password', '') !== '';

        return Inertia::render('Admin/Sales/Shipments/Create', [
            'order' => $order,
            'shiprocket_available' => $shiprocketConfigured,
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
            'shipment_mode' => 'required|string|in:manual,shiprocket',
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

            if (($validated['shipment_mode'] ?? 'manual') === 'shiprocket') {
                $shiprocketConfigured = (bool) $this->settingService->get('shipping.shiprocket.enabled', false)
                    && (string) $this->settingService->get('shipping.shiprocket.email', '') !== ''
                    && (string) $this->settingService->get('shipping.shiprocket.password', '') !== '';

                if (!$shiprocketConfigured) {
                    $shipment->delete();

                    return back()
                        ->withInput()
                        ->with('error', 'Shiprocket is not configured. Please complete Shiprocket settings first.');
                }

                try {
                    $shipment->loadMissing(['order.items', 'order.shippingAddress', 'shipmentItems.orderItem']);
                    $this->syncShipmentWithShiprocket($shipment);

                    return redirect()
                        ->route('admin.sales.shipments.show', $shipment->id)
                        ->with('success', 'Shipment created and sent to Shiprocket successfully.');
                } catch (\Throwable $shiprocketError) {
                    $shipment->delete();

                    return back()
                        ->withInput()
                        ->with('error', 'Shiprocket shipment creation failed: ' . $shiprocketError->getMessage());
                }
            }

            return redirect()
                ->route('admin.sales.shipments.show', $shipment->id)
                ->with('success', 'Shipment created successfully using manual flow.');
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

    /**
     * Create shipment order in Shiprocket.
     */
    public function createInShiprocket(int $id): RedirectResponse
    {
        try {
            $shipment = Shipment::with([
                'order.items',
                'order.shippingAddress',
                'shipmentItems.orderItem',
            ])->findOrFail($id);

            $this->syncShipmentWithShiprocket($shipment);

            return back()->with('success', 'Shipment created in Shiprocket successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Sync tracking status from Shiprocket.
     */
    public function syncShiprocketTracking(int $id): RedirectResponse
    {
        try {
            $shipment = Shipment::findOrFail($id);
            $awbCode = (string) ($shipment->shiprocket_awb_code ?: $shipment->tracking_number);

            if ($awbCode === '') {
                return back()->with('error', 'AWB/Tracking number is not available for this shipment.');
            }

            $result = $this->shiprocketService->fetchTrackingByAwb($awbCode);

            $updateData = [
                'shiprocket_awb_code' => $awbCode,
                'shiprocket_status' => $result['shiprocket_status'] ?? $shipment->shiprocket_status,
                'shiprocket_courier_name' => $result['shiprocket_courier_name'] ?? $shipment->shiprocket_courier_name,
                'shiprocket_tracking_payload' => $result['raw'] ?? null,
                'shiprocket_synced_at' => now(),
            ];

            if (!empty($updateData['shiprocket_courier_name'])) {
                $updateData['carrier'] = $updateData['shiprocket_courier_name'];
            }

            $mappedStatus = $this->mapShiprocketStatusToShipmentStatus($updateData['shiprocket_status'] ?? null);
            if ($mappedStatus !== null) {
                $updateData['status'] = $mappedStatus;
                if ($mappedStatus === Shipment::STATUS_DELIVERED && empty($shipment->delivered_at)) {
                    $updateData['delivered_at'] = now();
                }
                if (in_array($mappedStatus, [Shipment::STATUS_SHIPPED, Shipment::STATUS_IN_TRANSIT, Shipment::STATUS_OUT_FOR_DELIVERY], true) && empty($shipment->shipped_at)) {
                    $updateData['shipped_at'] = now();
                }
            }

            $shipment->update($updateData);

            return back()->with('success', 'Shiprocket tracking synced successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function mapShiprocketStatusToShipmentStatus(?string $shiprocketStatus): ?string
    {
        if (!is_string($shiprocketStatus) || trim($shiprocketStatus) === '') {
            return null;
        }

        $status = strtolower(trim($shiprocketStatus));

        if (str_contains($status, 'deliver')) {
            return Shipment::STATUS_DELIVERED;
        }

        if (str_contains($status, 'out for delivery')) {
            return Shipment::STATUS_OUT_FOR_DELIVERY;
        }

        if (str_contains($status, 'transit') || str_contains($status, 'in route')) {
            return Shipment::STATUS_IN_TRANSIT;
        }

        if (str_contains($status, 'ship') || str_contains($status, 'awb')) {
            return Shipment::STATUS_SHIPPED;
        }

        if (str_contains($status, 'cancel')) {
            return Shipment::STATUS_CANCELLED;
        }

        if (str_contains($status, 'fail') || str_contains($status, 'rto')) {
            return Shipment::STATUS_FAILED;
        }

        if (str_contains($status, 'pending') || str_contains($status, 'new')) {
            return Shipment::STATUS_PENDING;
        }

        return null;
    }

    private function syncShipmentWithShiprocket(Shipment $shipment): void
    {
        $result = $this->shiprocketService->createOrderForShipment($shipment);

        $updateData = [
            'shiprocket_order_id' => $result['shiprocket_order_id'] ?? $shipment->shiprocket_order_id,
            'shiprocket_shipment_id' => $result['shiprocket_shipment_id'] ?? $shipment->shiprocket_shipment_id,
            'shiprocket_awb_code' => $result['shiprocket_awb_code'] ?? $shipment->shiprocket_awb_code,
            'shiprocket_courier_name' => $result['shiprocket_courier_name'] ?? $shipment->shiprocket_courier_name,
            'shiprocket_status' => $result['shiprocket_status'] ?? $shipment->shiprocket_status,
            'shiprocket_tracking_payload' => $result['raw'] ?? null,
            'shiprocket_synced_at' => now(),
        ];

        if (!empty($updateData['shiprocket_awb_code'])) {
            $updateData['tracking_number'] = $updateData['shiprocket_awb_code'];
            $updateData['tracking_url'] = 'https://shiprocket.co/tracking/' . $updateData['shiprocket_awb_code'];
        }

        if (!empty($updateData['shiprocket_courier_name'])) {
            $updateData['carrier'] = $updateData['shiprocket_courier_name'];
        }

        $mappedStatus = $this->mapShiprocketStatusToShipmentStatus($updateData['shiprocket_status'] ?? null);
        if ($mappedStatus !== null) {
            $updateData['status'] = $mappedStatus;
        }

        $shipment->update($updateData);
    }
}
