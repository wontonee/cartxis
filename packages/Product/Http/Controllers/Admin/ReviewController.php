<?php

namespace Vortex\Product\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Product\Models\ProductReview;
use Vortex\Product\Models\Product;

class ReviewController extends Controller
{
    /**
     * Display a listing of product reviews.
     */
    public function index(Request $request): Response
    {
        $query = ProductReview::with(['product', 'user', 'adminReplier'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Search by title, comment, or reviewer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%")
                    ->orWhere('reviewer_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Get paginated results
        $reviews = $query->paginate(15)->through(function ($review) {
            return [
                'id' => $review->id,
                'product' => [
                    'id' => $review->product->id,
                    'name' => $review->product->name,
                    'slug' => $review->product->slug,
                ],
                'reviewer' => $review->reviewer_display_name,
                'user_id' => $review->user_id,
                'rating' => $review->rating,
                'title' => $review->title,
                'comment' => $review->comment,
                'status' => $review->status,
                'admin_reply' => $review->admin_reply,
                'admin_replier' => $review->adminReplier ? [
                    'id' => $review->adminReplier->id,
                    'name' => $review->adminReplier->name,
                ] : null,
                'admin_replied_at' => $review->admin_replied_at?->format('M d, Y H:i'),
                'helpful_count' => $review->helpful_count,
                'verified_purchase' => $review->verified_purchase,
                'created_at' => $review->created_at->format('M d, Y H:i'),
            ];
        });

        // Get statistics
        $stats = [
            'total' => ProductReview::count(),
            'pending' => ProductReview::pending()->count(),
            'approved' => ProductReview::approved()->count(),
            'rejected' => ProductReview::rejected()->count(),
        ];

        // Get products for filter dropdown
        $products = Product::select('id', 'name')
            ->whereHas('reviews')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'stats' => $stats,
            'products' => $products,
            'filters' => $request->only(['status', 'rating', 'product_id', 'search']),
        ]);
    }

    /**
     * Display the specified review.
     */
    public function show(ProductReview $review): Response
    {
        $review->load(['product', 'user', 'adminReplier']);

        return Inertia::render('Admin/Reviews/Show', [
            'review' => [
                'id' => $review->id,
                'product' => [
                    'id' => $review->product->id,
                    'name' => $review->product->name,
                    'slug' => $review->product->slug,
                    'price' => $review->product->price,
                ],
                'reviewer' => $review->reviewer_display_name,
                'reviewer_email' => $review->user?->email ?? $review->reviewer_email,
                'user_id' => $review->user_id,
                'rating' => $review->rating,
                'title' => $review->title,
                'comment' => $review->comment,
                'status' => $review->status,
                'admin_reply' => $review->admin_reply,
                'admin_replier' => $review->adminReplier ? [
                    'id' => $review->adminReplier->id,
                    'name' => $review->adminReplier->name,
                ] : null,
                'admin_replied_at' => $review->admin_replied_at?->format('M d, Y H:i'),
                'helpful_count' => $review->helpful_count,
                'verified_purchase' => $review->verified_purchase,
                'created_at' => $review->created_at->format('M d, Y H:i'),
                'updated_at' => $review->updated_at->format('M d, Y H:i'),
            ],
        ]);
    }

    /**
     * Update review status (approve/reject).
     */
    public function updateStatus(Request $request, ProductReview $review)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $review->status = $request->status;
        $review->save();

        return back()->with('success', "Review {$request->status} successfully.");
    }

    /**
     * Add admin reply to review.
     */
    public function reply(Request $request, ProductReview $review)
    {
        $request->validate([
            'reply' => 'required|string|max:1000',
        ]);

        $review->addReply($request->reply, auth()->id());

        return back()->with('success', 'Reply added successfully.');
    }

    /**
     * Bulk action on reviews (approve/reject/delete).
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:product_reviews,id',
        ]);

        $count = 0;

        DB::transaction(function () use ($request, &$count) {
            $reviews = ProductReview::whereIn('id', $request->ids)->get();

            foreach ($reviews as $review) {
                switch ($request->action) {
                    case 'approve':
                        $review->approve();
                        $count++;
                        break;
                    case 'reject':
                        $review->reject();
                        $count++;
                        break;
                    case 'delete':
                        $review->delete();
                        $count++;
                        break;
                }
            }
        });

        $actionLabel = ucfirst($request->action);
        return back()->with('success', "{$count} review(s) {$actionLabel}d successfully.");
    }

    /**
     * Remove the specified review.
     */
    public function destroy(ProductReview $review)
    {
        $review->delete();

        return redirect()
            ->route('admin.catalog.reviews.index')
            ->with('success', 'Review deleted successfully.');
    }
}
