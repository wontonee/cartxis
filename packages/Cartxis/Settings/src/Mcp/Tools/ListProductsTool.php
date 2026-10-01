<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Product\Models\Product;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;

class ListProductsTool implements McpToolInterface
{
    public function name(): string
    {
        return 'cartxis_list_products';
    }

    public function description(): string
    {
        return 'List catalog products with optional search and pagination (max 50 per page).';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => [
                    'type' => 'string',
                    'description' => 'Search by name or SKU',
                ],
                'page' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'description' => 'Page number (default 1)',
                ],
                'per_page' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 50,
                    'description' => 'Results per page (default 20, max 50)',
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

        $query = Product::query()->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return McpResponse::text([
            'data' => $paginator->getCollection()->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'status' => $product->status,
                'quantity' => $product->quantity,
                'type' => $product->type,
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
