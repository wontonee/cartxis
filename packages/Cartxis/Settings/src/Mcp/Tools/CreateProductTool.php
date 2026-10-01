<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Tools;

use Cartxis\Product\Models\Product;
use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Support\McpResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateProductTool implements McpToolInterface
{
    public function name(): string
    {
        return 'cartxis_create_product';
    }

    public function description(): string
    {
        return 'Create a product with minimal fields (name, sku, price, status). Defaults match admin ProductController.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
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
                'type' => [
                    'type' => 'string',
                    'enum' => ['simple', 'configurable', 'virtual', 'downloadable', 'quote'],
                ],
            ],
            'required' => ['name', 'price', 'status'],
            'additionalProperties' => false,
        ];
    }

    public function handle(array $arguments): array
    {
        try {
            $validated = Validator::make($arguments, [
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|unique:products,sku',
                'price' => 'required|numeric|min:0',
                'status' => 'required|in:enabled,disabled',
                'quantity' => 'nullable|integer|min:0',
                'short_description' => 'nullable|string',
                'description' => 'nullable|string',
                'type' => 'nullable|in:simple,configurable,virtual,downloadable,quote',
            ])->validate();
        } catch (ValidationException $e) {
            return McpResponse::error(json_encode($e->errors()));
        }

        $payload = array_merge([
            'visibility' => 'both',
            'quantity' => 0,
            'stock_status' => 'in_stock',
            'manage_stock' => true,
            'type' => 'simple',
            'featured' => false,
            'new' => false,
        ], $validated);

        $product = Product::create($payload);

        return McpResponse::text([
            'message' => 'Product created',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'status' => $product->status,
            ],
        ]);
    }
}
