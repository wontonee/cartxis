<?php

namespace Vortex\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vortex\API\Helpers\ApiResponse;
use Vortex\API\Http\Resources\ReviewResource;
use Vortex\Product\Models\ProductReview;
use Vortex\Product\Models\Product;

class ReviewController extends Controller
{
    /**
     * Get product reviews.
     */
    public function productReviews(Request $request, $productId)
    {
        $perPage = min($request->get('per_page', 20), config('vortex-api.pagination.max_per_page'));

        $product = Product::find($productId);

        if (!$product) {
            return ApiResponse::notFound('Product not found', 'PRODUCT_NOT_FOUND');
        }

        $reviews = ProductReview::with(['customer'])
            ->where('product_id', $productId)
            ->where('status', 'approved')
            ->latest()
            ->paginate($perPage);

        return ApiResponse::paginated(
            $reviews->through(fn($review) => new ReviewResource($review)),
            'Product reviews retrieved successfully'
        );
    }

    /**
     * Create product review.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'comment' => 'required|string|max:1000',
            'images' => 'nullable|array|max:' . config('vortex-api.uploads.review_images.max_count', 5),
            'images.*' => 'image|max:' . config('vortex-api.uploads.review_images.max_size', 5120),
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        // Check if user has purchased this product
        $hasPurchased = $request->user()->orders()
            ->whereHas('items', fn($q) => $q->where('product_id', $request->product_id))
            ->where('status', 'completed')
            ->exists();

        if (!$hasPurchased) {
            return ApiResponse::error(
                'You can only review products you have purchased',
                null,
                403,
                'PURCHASE_REQUIRED'
            );
        }

        // Check if user already reviewed
        $hasReviewed = ProductReview::where('customer_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($hasReviewed) {
            return ApiResponse::error(
                'You have already reviewed this product',
                null,
                400,
                'ALREADY_REVIEWED'
            );
        }

        $reviewData = [
            'customer_id' => $request->user()->id,
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'status' => 'pending', // Requires admin approval
        ];

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('reviews', 'public');
            }
            $reviewData['images'] = json_encode($imagePaths);
        }

        $review = ProductReview::create($reviewData);

        return ApiResponse::success(
            new ReviewResource($review),
            'Review submitted successfully. It will be published after approval.',
            201
        );
    }

    /**
     * Update review.
     */
    public function update(Request $request, $id)
    {
        $review = ProductReview::where('customer_id', $request->user()->id)->find($id);

        if (!$review) {
            return ApiResponse::notFound('Review not found', 'REVIEW_NOT_FOUND');
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'nullable|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $review->update($request->only(['rating', 'title', 'comment']));
        $review->update(['status' => 'pending']); // Re-submit for approval

        return ApiResponse::success(
            new ReviewResource($review),
            'Review updated successfully'
        );
    }

    /**
     * Delete review.
     */
    public function destroy(Request $request, $id)
    {
        $review = ProductReview::where('customer_id', $request->user()->id)->find($id);

        if (!$review) {
            return ApiResponse::notFound('Review not found', 'REVIEW_NOT_FOUND');
        }

        // Delete review images
        if ($review->images) {
            $images = json_decode($review->images, true);
            foreach ($images as $image) {
                \Storage::disk('public')->delete($image);
            }
        }

        $review->delete();

        return ApiResponse::success(null, 'Review deleted successfully');
    }

    /**
     * Vote on review (helpful/not helpful).
     */
    public function vote(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'vote' => 'required|in:helpful,not_helpful',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $review = ProductReview::find($id);

        if (!$review) {
            return ApiResponse::notFound('Review not found', 'REVIEW_NOT_FOUND');
        }

        // TODO: Implement vote tracking (prevent duplicate votes)
        // For now, just increment count

        if ($request->vote === 'helpful') {
            $review->increment('helpful_count');
        } else {
            $review->increment('not_helpful_count');
        }

        return ApiResponse::success([
            'helpful_count' => $review->helpful_count,
            'not_helpful_count' => $review->not_helpful_count,
        ], 'Vote recorded successfully');
    }
}
