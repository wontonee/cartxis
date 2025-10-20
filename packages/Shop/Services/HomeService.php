<?php

namespace Vortex\Shop\Services;

use Vortex\Shop\Contracts\ProductRepositoryInterface;
use Vortex\Shop\Contracts\CategoryRepositoryInterface;
use Vortex\Shop\Services\ShopService;

class HomeService extends ShopService
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Create a new service instance.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get homepage data.
     *
     * @return array
     */
    public function getHomepageData()
    {
        try {
            return $this->remember('homepage.data', 3600, function () {
                $featuredCount = config('shop.homepage.featured_products_count', 12);
                
                return [
                    'featured_products' => config('shop.homepage.show_featured_products', true)
                        ? $this->productRepository->getFeaturedProducts($featuredCount)
                        : collect([]),
                    
                    'new_products' => config('shop.homepage.show_new_products', true)
                        ? $this->productRepository->getNewProducts($featuredCount)
                        : collect([]),
                    
                    'categories' => config('shop.homepage.show_categories', true)
                        ? $this->categoryRepository->getRootCategories()
                        : collect([]),
                ];
            });
        } catch (\Exception $e) {
            $this->handleException($e, 'Error fetching homepage data');
        }
    }

    /**
     * Get featured products.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeaturedProducts($limit = null)
    {
        try {
            $limit = $limit ?? config('shop.homepage.featured_products_count', 12);
            
            return $this->remember("featured_products.{$limit}", 3600, function () use ($limit) {
                return $this->productRepository->getFeaturedProducts($limit);
            });
        } catch (\Exception $e) {
            $this->handleException($e, 'Error fetching featured products');
        }
    }

    /**
     * Get new products.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNewProducts($limit = null)
    {
        try {
            $limit = $limit ?? config('shop.homepage.featured_products_count', 12);
            
            return $this->remember("new_products.{$limit}", 1800, function () use ($limit) {
                return $this->productRepository->getNewProducts($limit);
            });
        } catch (\Exception $e) {
            $this->handleException($e, 'Error fetching new products');
        }
    }

    /**
     * Clear homepage cache.
     *
     * @return void
     */
    public function clearCache()
    {
        $this->forget('homepage.data');
        $this->forget('featured_products.*');
        $this->forget('new_products.*');
    }
}
