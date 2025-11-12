<?php

namespace Vortex\Shop\Services;

use Vortex\Shop\Contracts\ProductRepositoryInterface;
use Vortex\Shop\Contracts\CategoryRepositoryInterface;
use Vortex\Shop\Services\ShopService;
use Vortex\CMS\Models\Block;

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
                
                // Get active CMS blocks for homepage
                $blockIdentifiers = [
                    'homepage-hero',
                    'homepage-deal',
                    'homepage-features',
                    'homepage-testimonials',
                    'homepage-brands'
                ];
                
                $blocks = Block::whereIn('identifier', $blockIdentifiers)
                    ->where('status', true)
                    ->where(function ($query) {
                        $query->whereNull('start_date')
                            ->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($query) {
                        $query->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                    })
                    ->get()
                    ->keyBy('identifier');
                
                $heroBlock = $blocks->get('homepage-hero');
                $dealBlock = $blocks->get('homepage-deal');
                $featuresBlock = $blocks->get('homepage-features');
                $testimonialsBlock = $blocks->get('homepage-testimonials');
                $brandsBlock = $blocks->get('homepage-brands');
                
                // Get categories with product counts
                $categories = $this->categoryRepository->getRootCategories()
                    ->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                            'description' => $category->description,
                            'image' => $category->image,
                            'products_count' => $category->products()->count(),
                        ];
                    });
                
                return [
                    'featured_products' => config('shop.homepage.show_featured_products', true)
                        ? $this->productRepository->getFeaturedProducts($featuredCount)
                        : collect([]),
                    
                    'new_products' => config('shop.homepage.show_new_products', true)
                        ? $this->productRepository->getNewProducts($featuredCount)
                        : collect([]),
                    
                    'categories' => $categories,
                    
                    'blocks' => [
                        'hero' => $heroBlock ? [
                            'id' => $heroBlock->id,
                            'title' => $heroBlock->title,
                            'content' => $heroBlock->content,
                            'type' => $heroBlock->type,
                            'data' => $heroBlock->type === 'banner' ? json_decode($heroBlock->content, true) : null,
                        ] : null,
                        'deal' => $dealBlock ? [
                            'id' => $dealBlock->id,
                            'title' => $dealBlock->title,
                            'content' => $dealBlock->content,
                            'type' => $dealBlock->type,
                        ] : null,
                        'features' => $featuresBlock ? [
                            'id' => $featuresBlock->id,
                            'title' => $featuresBlock->title,
                            'content' => $featuresBlock->content,
                            'type' => $featuresBlock->type,
                        ] : null,
                        'testimonials' => $testimonialsBlock ? [
                            'id' => $testimonialsBlock->id,
                            'title' => $testimonialsBlock->title,
                            'content' => $testimonialsBlock->content,
                            'type' => $testimonialsBlock->type,
                        ] : null,
                        'brands' => $brandsBlock ? [
                            'id' => $brandsBlock->id,
                            'title' => $brandsBlock->title,
                            'content' => $brandsBlock->content,
                            'type' => $brandsBlock->type,
                        ] : null,
                    ],
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
