<?php

namespace Cartxis\Sales\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Cartxis\Shop\Models\Order;
use Cartxis\Sales\Services\OrderService;
use Cartxis\Sales\Repositories\OrderRepository;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected OrderRepository $orderRepository
    ) {}

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): InertiaResponse
    {
        $filters = $request->only([
            'search',
            'status',
            'payment_status',
            'payment_method',
            'date_from',
            'date_to',
            'total_min',
            'total_max',
            'customer_id',
            'shipping_method',
            'sort_by',
            'sort_order'
        ]);

        $orders = $this->orderRepository->getAdminOrders(
            $filters,
            $request->input('per_page', 15)
        );

        return Inertia::render('Admin/Sales/Orders/Index', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => $this->getOrderStatuses(),
            'paymentStatuses' => $this->getPaymentStatuses(),
        ]);
    }

    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        return Inertia::render('Admin/Sales/Orders/Create', [
            'customers' => \App\Models\User::select('id', 'name', 'email')->get(),
            'products' => \Cartxis\Product\Models\Product::select('id', 'name', 'price', 'quantity')->where('status', 'enabled')->get(),
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'customer_email' => 'required|email',
            'customer_phone' => 'nullable|string|max:20',
            'payment_method' => 'required|string',
            'shipping_method' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.product_name' => 'required|string',
            'shipping_address' => 'required|array',
            'shipping_address.first_name' => 'required|string',
            'shipping_address.last_name' => 'required|string',
            'shipping_address.phone' => 'required|string',
            'shipping_address.address_line1' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.state' => 'required|string',
            'shipping_address.postal_code' => 'required|string',
            'shipping_address.country' => 'required|string',
            'billing_address' => 'required|array',
            'billing_address.first_name' => 'required|string',
            'billing_address.last_name' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'shipping_cost' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        try {
            $order = $this->orderService->createOrder($validated);

            return redirect()
                ->route('admin.sales.orders.show', $order->id)
                ->with('success', 'Order created successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified order.
     */
    public function show(int $id): InertiaResponse|RedirectResponse
    {
        $order = $this->orderRepository->getOrderWithRelations($id);

        if (!$order) {
            return redirect()->route('admin.sales.orders.index')->with('error', 'Order not found');
        }

        return Inertia::render('Admin/Sales/Orders/Show', [
            'order' => $order,
            'statuses' => $this->getOrderStatuses(),
            'paymentStatuses' => $this->getPaymentStatuses(),
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled,refunded,failed',
            'comment' => 'nullable|string|max:1000',
            'notify_customer' => 'boolean',
        ]);

        $order = Order::findOrFail($id);

        $success = $this->orderService->updateStatus(
            $order,
            $validated['status'],
            $validated['comment'] ?? null,
            $validated['notify_customer'] ?? false
        );

        if ($success) {
            return back()->with('success', 'Order status updated successfully.');
        }

        return back()->with('error', 'Failed to update order status.');
    }

    /**
     * Update payment status.
     */
    public function updatePaymentStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'payment_status' => 'required|string|in:pending,paid,failed,refunded',
            'comment' => 'nullable|string|max:1000',
        ]);

        $order = Order::findOrFail($id);

        $success = $this->orderService->updatePaymentStatus(
            $order,
            $validated['payment_status'],
            $validated['comment'] ?? null
        );

        if ($success) {
            return back()->with('success', 'Payment status updated successfully.');
        }

        return back()->with('error', 'Failed to update payment status.');
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'restore_stock' => 'boolean',
        ]);

        $order = Order::findOrFail($id);

        $success = $this->orderService->cancelOrder(
            $order,
            $validated['reason'],
            $validated['restore_stock'] ?? true
        );

        if ($success) {
            return back()->with('success', 'Order cancelled successfully.');
        }

        return back()->with('error', 'Failed to cancel order.');
    }

    /**
     * Bulk update order status.
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
            'status' => 'required|string|in:pending,processing,completed,cancelled,refunded,failed',
            'comment' => 'nullable|string|max:1000',
            'notify_customer' => 'boolean',
        ]);

        $successCount = 0;
        $failedCount = 0;

        foreach ($validated['order_ids'] as $orderId) {
            $order = Order::find($orderId);
            
            if ($order) {
                $success = $this->orderService->updateStatus(
                    $order,
                    $validated['status'],
                    $validated['comment'] ?? null,
                    $validated['notify_customer'] ?? false
                );

                if ($success) {
                    $successCount++;
                } else {
                    $failedCount++;
                }
            } else {
                $failedCount++;
            }
        }

        return response()->json([
            'success' => $failedCount === 0,
            'message' => "Updated {$successCount} orders successfully." . 
                        ($failedCount > 0 ? " Failed to update {$failedCount} orders." : ''),
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ]);
    }

    /**
     * Export orders to CSV.
     */
    public function export(Request $request)
    {
        $filters = $request->only([
            'search',
            'status',
            'payment_status',
            'date_from',
            'date_to',
        ]);

        $orders = $this->orderRepository->getOrdersForExport($filters);

        $filename = 'orders_export_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Order Number',
                'Customer Name',
                'Customer Email',
                'Status',
                'Payment Status',
                'Payment Method',
                'Total',
                'Created At'
            ]);

            // CSV data
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->user->name ?? 'Guest',
                    $order->customer_email,
                    $order->status,
                    $order->payment_status,
                    $order->payment_method,
                    $order->total,
                    $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print order invoice (PDF).
     */
    public function printInvoice(int $id): InertiaResponse
    {
        $order = $this->orderRepository->getOrderWithRelations($id);

        if (!$order) {
            abort(404, 'Order not found');
        }

        return Inertia::render('Sales/Orders/PrintInvoice', [
            'order' => $order,
        ]);
    }

    /**
     * Print packing slip (PDF).
     */
    public function printPackingSlip(int $id): InertiaResponse
    {
        $order = $this->orderRepository->getOrderWithRelations($id);

        if (!$order) {
            abort(404, 'Order not found');
        }

        return Inertia::render('Sales/Orders/PrintPackingSlip', [
            'order' => $order,
        ]);
    }

    /**
     * Get order statistics for dashboard.
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->orderRepository->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get available order statuses.
     */
    protected function getOrderStatuses(): array
    {
        return [
            ['value' => Order::STATUS_PENDING, 'label' => 'Pending'],
            ['value' => Order::STATUS_PROCESSING, 'label' => 'Processing'],
            ['value' => Order::STATUS_COMPLETED, 'label' => 'Completed'],
            ['value' => Order::STATUS_CANCELLED, 'label' => 'Cancelled'],
            ['value' => Order::STATUS_REFUNDED, 'label' => 'Refunded'],
            ['value' => Order::STATUS_FAILED, 'label' => 'Failed'],
        ];
    }

    /**
     * Get available payment statuses.
     */
    protected function getPaymentStatuses(): array
    {
        return [
            ['value' => Order::PAYMENT_PENDING, 'label' => 'Pending'],
            ['value' => Order::PAYMENT_PAID, 'label' => 'Paid'],
            ['value' => Order::PAYMENT_FAILED, 'label' => 'Failed'],
            ['value' => Order::PAYMENT_REFUNDED, 'label' => 'Refunded'],
        ];
    }

    /**
     * Get available payment methods.
     */
    protected function getPaymentMethods(): array
    {
        return [
            ['value' => 'stripe', 'label' => 'Credit Card (Stripe)'],
            ['value' => 'paypal', 'label' => 'PayPal'],
            ['value' => 'cash_on_delivery', 'label' => 'Cash on Delivery'],
            ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
        ];
    }

    /**
     * Get available shipping methods.
     */
    protected function getShippingMethods(): array
    {
        return [
            ['value' => 'standard', 'label' => 'Standard Shipping'],
            ['value' => 'express', 'label' => 'Express Shipping'],
            ['value' => 'overnight', 'label' => 'Overnight Shipping'],
            ['value' => 'pickup', 'label' => 'Store Pickup'],
        ];
    }
}
