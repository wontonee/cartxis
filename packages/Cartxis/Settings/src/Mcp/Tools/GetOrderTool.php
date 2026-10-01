<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;
use Cartxis\Shop\Models\Order;

class GetOrderTool implements McpToolInterface
{
    public function name(): string
    {
        return 'cartxis_get_order';
    }

    public function description(): string
    {
        return 'Get an order by id or order_number (increment id).';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => 'Order ID',
                ],
                'increment_id' => [
                    'type' => 'string',
                    'description' => 'Order number / increment id',
                ],
                'order_number' => [
                    'type' => 'string',
                    'description' => 'Alias for increment_id',
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $id = $arguments['id'] ?? null;
        $number = trim((string) ($arguments['increment_id'] ?? $arguments['order_number'] ?? ''));

        if (! $id && $number === '') {
            return McpResponse::error('Provide id or increment_id');
        }

        $order = null;
        if ($id) {
            $order = Order::query()->with(['items'])->find((int) $id);
        } elseif ($number !== '') {
            $order = Order::query()->with(['items'])->where('order_number', $number)->first();
        }

        if (! $order) {
            return McpResponse::error('Order not found');
        }

        $items = [];
        if ($order->relationLoaded('items')) {
            $items = $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id ?? null,
                'product_name' => $item->product_name ?? $item->name ?? null,
                'sku' => $item->sku ?? null,
                'quantity' => $item->quantity ?? null,
                'price' => $item->price ?? null,
                'total' => $item->total ?? null,
            ])->values()->all();
        }

        return McpResponse::text([
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status ?? null,
            'payment_method' => $order->payment_method ?? null,
            'shipping_method' => $order->shipping_method ?? null,
            'customer_email' => $order->customer_email ?? null,
            'customer_phone' => $order->customer_phone ?? null,
            'subtotal' => $order->subtotal ?? null,
            'tax' => $order->tax ?? null,
            'shipping_cost' => $order->shipping_cost ?? null,
            'discount' => $order->discount ?? null,
            'total' => $order->total ?? null,
            'notes' => $order->notes ?? null,
            'items' => $items,
            'created_at' => optional($order->created_at)?->toIso8601String(),
            'updated_at' => optional($order->updated_at)?->toIso8601String(),
        ]);
    }
}
