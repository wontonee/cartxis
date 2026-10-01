<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Sales\Services\OrderService;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;
use Cartxis\Shop\Models\Order;

class UpdateOrderStatusTool implements McpToolInterface
{
    /**
     * Allowed transitions for MVP (refuse invalid ones).
     *
     * @var array<string, list<string>>
     */
    private const TRANSITIONS = [
        Order::STATUS_PENDING => [Order::STATUS_PROCESSING, Order::STATUS_CANCELLED, Order::STATUS_FAILED],
        Order::STATUS_PROCESSING => [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED, Order::STATUS_REFUNDED],
        Order::STATUS_COMPLETED => [Order::STATUS_REFUNDED],
        Order::STATUS_CANCELLED => [],
        Order::STATUS_REFUNDED => [],
        Order::STATUS_FAILED => [Order::STATUS_PENDING, Order::STATUS_CANCELLED],
    ];

    public function __construct(
        protected OrderService $orderService
    ) {}

    public function name(): string
    {
        return 'cartxis_update_order_status';
    }

    public function description(): string
    {
        return 'Update an order status using allowed Sales transitions. Refuses invalid transitions.';
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
                'status' => [
                    'type' => 'string',
                    'enum' => ['pending', 'processing', 'completed', 'cancelled', 'refunded', 'failed'],
                ],
                'comment' => [
                    'type' => 'string',
                    'description' => 'Optional history comment',
                ],
                'notify_customer' => [
                    'type' => 'boolean',
                ],
            ],
            'required' => ['id', 'status'],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $id = (int) ($arguments['id'] ?? 0);
        $status = (string) ($arguments['status'] ?? '');
        $comment = isset($arguments['comment']) ? (string) $arguments['comment'] : null;
        $notify = (bool) ($arguments['notify_customer'] ?? false);

        $allowedStatuses = array_keys(Order::getStatuses());
        if (! in_array($status, $allowedStatuses, true)) {
            return McpResponse::error('Invalid status. Allowed: '.implode(', ', $allowedStatuses));
        }

        $order = Order::query()->find($id);
        if (! $order) {
            return McpResponse::error('Order not found');
        }

        $current = (string) $order->status;
        if ($current === $status) {
            return McpResponse::text([
                'message' => 'Order already has this status',
                'order_id' => $order->id,
                'status' => $current,
            ]);
        }

        $allowedNext = self::TRANSITIONS[$current] ?? [];
        if (! in_array($status, $allowedNext, true)) {
            return McpResponse::error(
                "Invalid transition from {$current} to {$status}. Allowed: "
                .(empty($allowedNext) ? '(none)' : implode(', ', $allowedNext))
            );
        }

        $success = $this->orderService->updateStatus($order, $status, $comment, $notify);
        if (! $success) {
            return McpResponse::error('Failed to update order status');
        }

        $order->refresh();

        return McpResponse::text([
            'message' => 'Order status updated',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status_from' => $current,
            'status_to' => $order->status,
        ]);
    }
}
