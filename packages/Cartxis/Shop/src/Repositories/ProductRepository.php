<?php

namespace Cartxis\Shop\Repositories;

use Cartxis\Shop\Contracts\ProductRepositoryInterface;
use Cartxis\Product\Models\Product;

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
            ->with(['images', 'mainImage', 'categories'])
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
            ->with(['images', 'mainImage', 'categories'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get products currently on sale.
     */
    public function getOnSaleProducts($limit = 12)
    {
        return $this->model
            ->where('status', 'enabled')
            ->where('quantity', '>', 0)
            ->whereNotNull('special_price')
            ->whereColumn('special_price', '<', 'price')
            ->with(['images', 'mainImage', 'categories'])
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Resolve products by ID or slug, preserving reference order.
     */
    public function resolveProductReferences(array $references)
    {
        if ($references === []) {
            return collect([]);
        }

        $ids = [];
        $slugs = [];

        foreach ($references as $reference) {
            if (($reference['type'] ?? '') === 'id') {
                $ids[] = (int) $reference['value'];
            } elseif (($reference['type'] ?? '') === 'slug') {
                $slugs[] = (string) $reference['value'];
            }
        }

        $products = $this->model
            ->where('status', 'enabled')
            ->where('quantity', '>', 0)
            ->with(['images', 'mainImage', 'categories'])
            ->where(function ($query) use ($ids, $slugs) {
                if ($ids !== []) {
                    $query->whereIn('id', $ids);
                }

                if ($slugs !== []) {
                    $method = $ids !== [] ? 'orWhereIn' : 'whereIn';
                    $query->{$method}('slug', $slugs);
                }
            })
            ->get();

        $ordered = collect();

        foreach ($references as $reference) {
            $match = $products->first(function ($product) use ($reference) {
                if (($reference['type'] ?? '') === 'id') {
                    return (int) $product->id === (int) $reference['value'];
                }

                return $product->slug === ($reference['value'] ?? null);
            });

            if ($match) {
                $ordered->push($match);
            }
        }

        return $ordered;
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
            ->with(['images', 'categories', 'brand', 'attributeOptions', 'attributeValues.attribute', 'approvedReviews'])
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
            ->where('price', '>', 0)
            ->where('quantity', '>', 0)
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
            ->where('price', '>', 0)
            ->where('quantity', '>', 0)
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
