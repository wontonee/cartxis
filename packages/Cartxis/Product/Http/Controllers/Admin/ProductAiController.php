<?php

namespace Cartxis\Product\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cartxis\Product\Models\Product;
use Cartxis\Product\Services\AiProductDescriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductAiController extends Controller
{
    public function __construct(
        protected AiProductDescriptionService $service
    ) {}

    public function generateDescription(Request $request, Product $product): JsonResponse
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

        try {
            $result = $this->service->generate($product, $validated);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
