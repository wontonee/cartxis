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
            return $this->remember("category.{$slug}", 3600, function () use ($slug) {
                return $this->categoryRepository->findBySlug($slug);
            });
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
            return $this->remember('navigation.categories', 7200, function () {
                return $this->categoryRepository->getRootCategories();
            });
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
            return $this->remember("category.children.{$parentId}", 3600, function () use ($parentId) {
                return $this->categoryRepository->getChildren($parentId);
            });
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
            return $this->remember('all.categories', 7200, function () {
                return $this->categoryRepository->getActiveCategories();
            });
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
        }
        
        $this->forget('navigation.categories');
        $this->forget('all.categories');
    }
}
