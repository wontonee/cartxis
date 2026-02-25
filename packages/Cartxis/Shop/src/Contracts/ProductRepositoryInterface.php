<?php

namespace Cartxis\Shop\Contracts;

interface ProductRepositoryInterface extends ShopRepositoryInterface
{
    /**
     * Get featured products.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeaturedProducts($limit = 12);

    /**
     * Get new products.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNewProducts($limit = 12);

    /**
     * Get product by slug.
     *
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findBySlug($slug);

    /**
     * Get products by category.
     *
     * @param int $categoryId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByCategory($categoryId, $perPage = 12);

    /**
     * Search products.
     *
     * @param string $query
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search($query, $perPage = 12);

    /**
     * Get related products.
     *
     * @param int $productId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedProducts($productId, $limit = 4);

    /**
     * Get product with reviews.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getWithReviews($id);
}
