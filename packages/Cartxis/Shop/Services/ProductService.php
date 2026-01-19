<?php

namespace Cartxis\Shop\Services;

use Cartxis\Shop\Contracts\ProductRepositoryInterface;
use Cartxis\Shop\Services\ShopService;

class ProductService extends ShopService
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Create a new service instance.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get product by slug.
     *
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getProductBySlug($slug)
    {
        try {
            return $this->remember("product.{$slug}", 3600, function () use ($slug) {
                return $this->productRepository->findBySlug($slug);
            });
        } catch (\Exception $e) {
            $this->handleException($e, "Error fetching product: {$slug}");
        }
    }

    /**
     * Get product with reviews.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getProductWithReviews($id)
    {
        try {
            return $this->productRepository->getWithReviews($id);
        } catch (\Exception $e) {
            $this->handleException($e, "Error fetching product reviews: {$id}");
        }
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
        try {
            return $this->remember("related_products.{$productId}.{$limit}", 3600, function () use ($productId, $limit) {
                return $this->productRepository->getRelatedProducts($productId, $limit);
            });
        } catch (\Exception $e) {
            $this->handleException($e, "Error fetching related products for: {$productId}");
        }
    }

    /**
     * Search products.
     *
     * @param string $query
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function searchProducts($query, $perPage = null)
    {
        try {
            $perPage = $perPage ?? config('shop.listing.products_per_page', 12);
            
            return $this->productRepository->search($query, $perPage);
        } catch (\Exception $e) {
            $this->handleException($e, "Error searching products: {$query}");
        }
    }

    /**
     * Get products by category.
     *
     * @param int $categoryId
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getProductsByCategory($categoryId, array $filters = [])
    {
        try {
            $perPage = $filters['per_page'] ?? config('shop.listing.products_per_page', 12);
            
            return $this->productRepository->getByCategory($categoryId, $perPage);
        } catch (\Exception $e) {
            $this->handleException($e, "Error fetching products for category: {$categoryId}");
        }
    }

    /**
     * Get all products with pagination and filters.
     *
     * @param int $perPage
     * @param string $sort
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllProducts($perPage = 12, $sort = 'newest', $filters = [])
    {
        try {
            // Use the Product model directly to start the query
            $query = \Cartxis\Product\Models\Product::query()
                ->where('status', 'enabled')
                ->where('price', '>', 0)
                ->where('quantity', '>', 0);

            // Filter by category
            if (!empty($filters['category'])) {
                $query->whereHas('categories', function($q) use ($filters) {
                    $q->where('slug', $filters['category']);
                });
            }

            // Filter by brand
            if (!empty($filters['brand'])) {
                $brand = \Cartxis\Product\Models\Brand::where('slug', $filters['brand'])->first();
                if ($brand) {
                    $query->where('brand_id', $brand->id);
                }
            }

            // Filter by search
            if (!empty($filters['search'])) {
                $searchTerm = $filters['search'];
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('sku', 'like', "%{$searchTerm}%");
                });
            }

            // Filter by price range
            if (!empty($filters['price_min'])) {
                $query->where('price', '>=', $filters['price_min']);
            }
            if (!empty($filters['price_max'])) {
                $query->where('price', '<=', $filters['price_max']);
            }

            // Filter by stock
            if (!empty($filters['in_stock'])) {
                $query->where('quantity', '>', 0)
                      ->where('stock_status', 'in_stock');
            }

            // Apply sorting
            switch ($sort) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
                case 'rating':
                    // Rating is a computed attribute from reviews, so we need to use a subquery
                    $query->withAvg('approvedReviews as rating_avg', 'rating')
                          ->orderByDesc('rating_avg');
                    break;
                case 'featured':
                    $query->orderBy('featured', 'desc')
                          ->orderBy('created_at', 'desc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            return $query->paginate($perPage);
        } catch (\Exception $e) {
            $this->handleException($e, "Error fetching all products");
            // Return empty paginator on error
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }
    }

    /**
     * Clear product cache.
     *
     * @param string|null $slug
     * @return void
     */
    public function clearCache($slug = null)
    {
        if ($slug) {
            $this->forget("product.{$slug}");
        } else {
            cache()->flush();
        }
    }
}
