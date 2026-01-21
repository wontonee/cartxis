<?php

namespace Cartxis\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Cartxis\API\Helpers\ApiResponse;
use Cartxis\Product\Models\Product;
use Cartxis\Product\Services\AiProductDescriptionService;
use Illuminate\Http\Request;

class ProductAiController extends Controller
{
    public function __construct(
        protected AiProductDescriptionService $service
    ) {}

    public function generateDescription(Request $request, $productId)
    {
        $validated = $request->validate([
            'product_title' => 'nullable|string|max:200',
            'category' => 'nullable|string|max:255',
            'attributes' => 'nullable|array',
            'images' => 'nullable|array',
            'brand' => 'nullable|string|max:120',
            'key_features' => 'nullable|array',
            'target_audience' => 'nullable|string|max:120',
            'tone_preference' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10',
            'agent' => 'nullable|string|max:120',
        ]);

        $product = Product::find($productId);
        if (!$product) {
            return ApiResponse::notFound('Product not found', 'PRODUCT_NOT_FOUND');
        }

        try {
            $result = $this->service->generate($product, $validated);
            return ApiResponse::success($result, 'Product description generated');
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), null, 422, 'AI_GENERATION_FAILED');
        }
    }
}
