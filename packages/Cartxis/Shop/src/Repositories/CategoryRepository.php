<?php

namespace Cartxis\Shop\Repositories;

use Cartxis\Shop\Contracts\CategoryRepositoryInterface;
use Cartxis\Product\Models\Category;

class CategoryRepository extends ShopRepository implements CategoryRepositoryInterface
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Category::class;
    }

    /**
     * Get root categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRootCategories()
    {
        return $this->model
            ->whereNull('parent_id')
            ->where('status', 'enabled')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get category by slug.
     *
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findBySlug($slug)
    {
        return $this->model
            ->where('slug', $slug)
            ->where('status', 1)
            ->first();
    }

    /**
     * Get category with products.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getWithProducts($id)
    {
        return $this->model
            ->with(['products' => function ($query) {
                $query->where('status', 1);
            }])
            ->find($id);
    }

    /**
     * Get category children.
     *
     * @param int $parentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChildren($parentId)
    {
        return $this->model
            ->where('parent_id', $parentId)
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get all active categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveCategories()
    {
        return $this->model
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get();
    }
}
