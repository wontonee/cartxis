<?php

namespace Vortex\Shop\Contracts;

interface CategoryRepositoryInterface extends ShopRepositoryInterface
{
    /**
     * Get root categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRootCategories();

    /**
     * Get category by slug.
     *
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findBySlug($slug);

    /**
     * Get category with products.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getWithProducts($id);

    /**
     * Get category children.
     *
     * @param int $parentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChildren($parentId);

    /**
     * Get all active categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveCategories();
}
