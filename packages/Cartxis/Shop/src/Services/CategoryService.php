<?php

namespace Cartxis\Shop\Services;

use Cartxis\Shop\Contracts\CategoryRepositoryInterface;
use Cartxis\Shop\Services\ShopService;

class CategoryService extends ShopService
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Create a new service instance.
     *
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get category by slug.
     *
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getCategoryBySlug($slug)
    {
        try {
            $categoryId = $this->remember("category.v2.{$slug}", 3600, function () use ($slug) {
                return $this->categoryRepository->findBySlug($slug)?->id;
            });

            if (! $categoryId) {
                return null;
            }

            return $this->categoryRepository->find($categoryId);
        } catch (\Exception $e) {
            $this->handleException($e, "Error fetching category: {$slug}");
        }
    }

    /**
     * Get category with products.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getCategoryWithProducts($id)
    {
        try {
            return $this->categoryRepository->getWithProducts($id);
        } catch (\Exception $e) {
            $this->handleException($e, "Error fetching category products: {$id}");
        }
    }

    /**
     * Get root categories for navigation.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNavigationCategories()
    {
        try {
            $ids = $this->remember('navigation.categories.v2', 7200, function () {
                return $this->categoryRepository->getRootCategories()->pluck('id')->all();
            });

            if (empty($ids)) {
                return collect();
            }

            return $this->categoryRepository
                ->findWhereIn('id', $ids)
                ->sortBy(fn ($category) => array_search($category->id, $ids, true))
                ->values();
        } catch (\Exception $e) {
            $this->handleException($e, 'Error fetching navigation categories');
        }
    }

    /**
     * Get category children.
     *
     * @param int $parentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoryChildren($parentId)
    {
        try {
            $ids = $this->remember("category.children.v2.{$parentId}", 3600, function () use ($parentId) {
                return $this->categoryRepository->getChildren($parentId)->pluck('id')->all();
            });

            if (empty($ids)) {
                return collect();
            }

            return $this->categoryRepository
                ->findWhereIn('id', $ids)
                ->sortBy(fn ($category) => array_search($category->id, $ids, true))
                ->values();
        } catch (\Exception $e) {
            $this->handleException($e, "Error fetching category children: {$parentId}");
        }
    }

    /**
     * Get all active categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCategories()
    {
        try {
            $ids = $this->remember('all.categories.v2', 7200, function () {
                return $this->categoryRepository->getActiveCategories()->pluck('id')->all();
            });

            if (empty($ids)) {
                return collect();
            }

            return $this->categoryRepository
                ->findWhereIn('id', $ids)
                ->sortBy(fn ($category) => array_search($category->id, $ids, true))
                ->values();
        } catch (\Exception $e) {
            $this->handleException($e, 'Error fetching all categories');
        }
    }

    /**
     * Clear category cache.
     *
     * @param string|null $slug
     * @return void
     */
    public function clearCache($slug = null)
    {
        if ($slug) {
            $this->forget("category.{$slug}");
            $this->forget("category.v2.{$slug}");
        }

        $this->forget('navigation.categories');
        $this->forget('navigation.categories.v2');
        $this->forget('all.categories');
        $this->forget('all.categories.v2');
    }
}
