<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Product\Models\Product;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateProductTool implements McpToolInterface
{
    public function name(): string
    {
        return 'cartxis_update_product';
    }

    public function description(): string
    {
        return 'Update a product by id. Only provided fields are changed.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'name' => ['type' => 'string'],
                'sku' => ['type' => 'string'],
                'price' => ['type' => 'number'],
                'status' => [
                    'type' => 'string',
                    'enum' => ['enabled', 'disabled'],
                ],
                'quantity' => ['type' => 'integer'],
                'short_description' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'visibility' => [
                    'type' => 'string',
                    'enum' => ['catalog', 'search', 'both', 'none'],
                ],
                'stock_status' => [
                    'type' => 'string',
                    'enum' => ['in_stock', 'out_of_stock', 'on_backorder'],
                ],
                'special_price' => ['type' => 'number'],
            ],
            'required' => ['id'],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        $id = (int) ($arguments['id'] ?? 0);
        $product = Product::query()->find($id);

        if (! $product) {
            return McpResponse::error('Product not found');
        }

        try {
            $validated = Validator::make($arguments, [
                'id' => 'required|integer',
                'name' => 'sometimes|string|max:255',
                'sku' => 'sometimes|nullable|string|unique:products,sku,'.$product->id,
                'price' => 'sometimes|numeric|min:0',
                'status' => 'sometimes|in:enabled,disabled',
                'quantity' => 'sometimes|integer|min:0',
                'short_description' => 'sometimes|nullable|string',
                'description' => 'sometimes|nullable|string',
                'visibility' => 'sometimes|in:catalog,search,both,none',
                'stock_status' => 'sometimes|in:in_stock,out_of_stock,on_backorder',
                'special_price' => 'sometimes|nullable|numeric|min:0',
            ])->validate();
        } catch (ValidationException $e) {
            return McpResponse::error(json_encode($e->errors()));
        }

        unset($validated['id']);
        $product->update($validated);
        $product->refresh();

        return McpResponse::text([
            'message' => 'Product updated',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'status' => $product->status,
                'quantity' => $product->quantity,
            ],
        ]);
    }
}
