<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;
use Cartxis\Shop\Models\Order;

class ListOrdersTool implements McpToolInterface
{
    public function name(): string
    {
        return 'cartxis_list_orders';
    }

    public function description(): string
    {
        return 'List orders with optional status filter and pagination.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => [
                    'type' => 'string',
                    'enum' => ['pending', 'processing', 'completed', 'cancelled', 'refunded', 'failed'],
                ],
                'page' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
                'per_page' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 50,
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $page = max(1, (int) ($arguments['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($arguments['per_page'] ?? 20)));
        $status = isset($arguments['status']) ? (string) $arguments['status'] : '';

        $query = Order::query()->orderByDesc('id');

        if ($status !== '') {
            $allowed = array_keys(Order::getStatuses());
            if (! in_array($status, $allowed, true)) {
                return McpResponse::error('Invalid status. Allowed: '.implode(', ', $allowed));
            }
            $query->where('status', $status);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return McpResponse::text([
            'data' => $paginator->getCollection()->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status ?? null,
                'customer_email' => $order->customer_email ?? null,
                'total' => $order->total ?? null,
                'created_at' => optional($order->created_at)?->toIso8601String(),
            ])->values()->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
