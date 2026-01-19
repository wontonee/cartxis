<?php

namespace Cartxis\Reports\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'statistics' => $this->resource['statistics'],
            'revenueChart' => $this->resource['revenueChart'],
            'ordersChart' => $this->resource['ordersChart'],
            'paymentChart' => $this->resource['paymentChart'],
            'topOrders' => $this->resource['topOrders']->map(function ($order) {
                return [
                    'id' => $order->id,
                    'increment_id' => $order->increment_id ?? $order->id,
                    'customer_name' => $order->customer?->name ?? $order->customer_name ?? 'Guest',
                    'total' => number_format($order->total, 2),
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('M d, Y'),
                ];
            }),
            'filters' => [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'status' => $request->input('status'),
                'payment_method' => $request->input('payment_method'),
            ],
        ];
    }
}
