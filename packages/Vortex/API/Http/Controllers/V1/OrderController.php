<?php

namespace Vortex\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Vortex\API\Helpers\ApiResponse;
use Vortex\API\Http\Resources\OrderResource;
use Vortex\Sales\Models\Order;

class OrderController extends Controller
{
    /**
     * Get customer orders.
     */
    public function index(Request $request)
    {
        $perPage = min($request->get('per_page', 20), config('vortex-api.pagination.max_per_page'));

        $query = Order::where('customer_id', $request->user()->id)
            ->with(['items.product', 'shippingAddress', 'billingAddress']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate($perPage);

        return ApiResponse::paginated(
            $orders->through(fn($order) => new OrderResource($order)),
            'Orders retrieved successfully'
        );
    }

    /**
     * Get single order details.
     */
    public function show(Request $request, $id)
    {
        $order = Order::with([
            'items.product.images',
            'shippingAddress',
            'billingAddress',
            'shipments',
            'invoices',
        ])
            ->where('customer_id', $request->user()->id)
            ->find($id);

        if (!$order) {
            return ApiResponse::notFound('Order not found', 'ORDER_NOT_FOUND');
        }

        return ApiResponse::success(
            new OrderResource($order),
            'Order details retrieved successfully'
        );
    }

    /**
     * Cancel order.
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::where('customer_id', $request->user()->id)->find($id);

        if (!$order) {
            return ApiResponse::notFound('Order not found', 'ORDER_NOT_FOUND');
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return ApiResponse::error(
                'Order cannot be cancelled',
                null,
                400,
                'ORDER_CANNOT_BE_CANCELLED'
            );
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // TODO: Restore product inventory
        // TODO: Process refund if payment was captured

        return ApiResponse::success(
            new OrderResource($order),
            'Order cancelled successfully'
        );
    }

    /**
     * Get order invoice.
     */
    public function invoice(Request $request, $id)
    {
        $order = Order::with(['items.product', 'shippingAddress', 'billingAddress'])
            ->where('customer_id', $request->user()->id)
            ->find($id);

        if (!$order) {
            return ApiResponse::notFound('Order not found', 'ORDER_NOT_FOUND');
        }

        // TODO: Generate PDF invoice
        // For now, return invoice data

        return ApiResponse::success([
            'order' => new OrderResource($order),
            'invoice_url' => route('api.orders.invoice.pdf', $order->id),
        ], 'Invoice retrieved successfully');
    }

    /**
     * Track order.
     */
    public function track(Request $request, $id)
    {
        $order = Order::with(['shipments.trackingInfo'])
            ->where('customer_id', $request->user()->id)
            ->find($id);

        if (!$order) {
            return ApiResponse::notFound('Order not found', 'ORDER_NOT_FOUND');
        }

        $trackingInfo = [];

        if ($order->shipments->isNotEmpty()) {
            $shipment = $order->shipments->first();
            $trackingInfo = [
                'tracking_number' => $shipment->tracking_number ?? null,
                'carrier' => $shipment->carrier ?? null,
                'status' => $shipment->status ?? null,
                'shipped_at' => $shipment->shipped_at?->toIso8601String(),
                'estimated_delivery' => $shipment->estimated_delivery?->toIso8601String(),
                'tracking_url' => $shipment->tracking_url ?? null,
            ];
        }

        return ApiResponse::success([
            'order_id' => $order->id,
            'order_number' => $order->increment_id,
            'status' => $order->status,
            'tracking' => $trackingInfo,
        ], 'Order tracking information retrieved');
    }
}
