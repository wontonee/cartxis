<?php

namespace Vortex\Shop\Repositories;

use Vortex\Shop\Contracts\ProductRepositoryInterface;
use Vortex\Product\Models\Product;

class ProductRepository extends ShopRepository implements ProductRepositoryInterface
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Product::class;
    }

    /**
     * Get featured products.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeaturedProducts($limit = 12)
    {
        return $this->model
            ->where('featured', 1)
            ->where('status', 'enabled')
            ->where('quantity', '>', 0)
            ->limit($limit)
            ->get();
    }

    /**
     * Get new products.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNewProducts($limit = 12)
    {
        return $this->model
            ->where('status', 'enabled')
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Find product by slug.
     *
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findBySlug($slug)
    {
        return $this->model
            ->where('slug', $slug)
            ->where('status', 'enabled')
            ->with(['images', 'categories', 'brand', 'attributeOptions', 'attributeValues.attribute'])
            ->withAvg('approvedReviews as rating', 'rating')
            ->withCount('approvedReviews as reviews_count')
            ->first();
    }

    /**
     * Get products by category.
     *
     * @param int $categoryId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByCategory($categoryId, $perPage = 12)
    {
        return $this->model
            ->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->where('status', 'enabled')
            ->paginate($perPage);
    }

    /**
     * Search products.
     *
     * @param string $query
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search($query, $perPage = 12)
    {
        return $this->model
            ->where('status', 'enabled')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Get related products.
     *
     * @param int $productId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedProducts($productId, $limit = 4)
    {
        $product = $this->find($productId);

        if (!$product) {
            return collect([]);
        }

        $categoryIds = $product->categories->pluck('id')->toArray();

        return $this->model
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds);
            })
            ->where('id', '!=', $productId)
            ->where('status', 'enabled')
            ->where('quantity', '>', 0)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Get product with reviews.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getWithReviews($id)
    {
        return $this->model
            ->with(['reviews' => function ($query) {
                $query->where('status', 'approved')
                      ->orderBy('created_at', 'desc');
            }])
            ->find($id);
    }
}
