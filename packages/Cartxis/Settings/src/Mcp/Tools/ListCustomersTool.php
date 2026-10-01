<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Customer\Models\Customer;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;

class ListCustomersTool implements McpToolInterface
{
    public function name(): string
    {
        return 'cartxis_list_customers';
    }

    public function description(): string
    {
        return 'List customers with optional search and pagination.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => [
                    'type' => 'string',
                    'description' => 'Search by name, email, or phone',
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
        $search = isset($arguments['search']) ? trim((string) $arguments['search']) : '';

        $query = Customer::query()->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return McpResponse::text([
            'data' => $paginator->getCollection()->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'is_active' => $customer->is_active,
                'is_guest' => $customer->is_guest,
                'total_orders' => $customer->total_orders,
                'total_spent' => $customer->total_spent,
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
