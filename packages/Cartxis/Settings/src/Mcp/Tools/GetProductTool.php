<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Product\Models\Product;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;

class GetProductTool implements McpToolInterface
{
    public function name(): string
    {
        return 'cartxis_get_product';
    }

    public function description(): string
    {
        return 'Get a single product by id or sku.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => 'Product ID',
                ],
                'sku' => [
                    'type' => 'string',
                    'description' => 'Product SKU',
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $id = $arguments['id'] ?? null;
        $sku = isset($arguments['sku']) ? trim((string) $arguments['sku']) : '';

        if (! $id && $sku === '') {
            return McpResponse::error('Provide id or sku');
        }

        $product = null;
        if ($id) {
            $product = Product::query()->find((int) $id);
        } elseif ($sku !== '') {
            $product = Product::query()->where('sku', $sku)->first();
        }

        if (! $product) {
            return McpResponse::error('Product not found');
        }

        return McpResponse::text([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'slug' => $product->slug,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'price' => $product->price,
            'special_price' => $product->special_price,
            'cost' => $product->cost,
            'status' => $product->status,
            'visibility' => $product->visibility,
            'quantity' => $product->quantity,
            'stock_status' => $product->stock_status,
            'manage_stock' => $product->manage_stock,
            'type' => $product->type,
            'weight' => $product->weight,
            'featured' => $product->featured,
            'new' => $product->new,
            'meta_title' => $product->meta_title,
            'meta_description' => $product->meta_description,
            'created_at' => optional($product->created_at)?->toIso8601String(),
            'updated_at' => optional($product->updated_at)?->toIso8601String(),
        ]);
    }
}
