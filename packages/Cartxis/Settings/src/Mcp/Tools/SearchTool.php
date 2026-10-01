<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Customer\Models\Customer;
use Cartxis\Product\Models\Product;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;
use Cartxis\Shop\Models\Order;

class SearchTool implements McpToolInterface
{
    public function name(): string
    {
        return 'cartxis_search';
    }

    public function description(): string
    {
        return 'Unified search across products, orders, and customers by query string.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Search query',
                ],
                'limit' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 25,
                    'description' => 'Max results per entity type (default 10)',
                ],
            ],
            'required' => ['query'],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));
        if ($query === '') {
            return McpResponse::error('query is required');
        }

        $limit = min(25, max(1, (int) ($arguments['limit'] ?? 10)));

        $products = Product::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'name', 'sku', 'price', 'status']);

        $orders = Order::query()
            ->where(function ($q) use ($query) {
                $q->where('order_number', 'like', "%{$query}%")
                    ->orWhere('customer_email', 'like', "%{$query}%");
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'order_number', 'status', 'customer_email', 'total']);

        $customers = Customer::query()
            ->where(function ($q) use ($query) {
                $q->where('email', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'first_name', 'last_name', 'email', 'phone']);

        return McpResponse::text([
            'query' => $query,
            'products' => $products,
            'orders' => $orders,
            'customers' => $customers,
        ]);
    }
}
