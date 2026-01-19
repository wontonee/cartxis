<?php

namespace Cartxis\Shop\Services;

use Cartxis\Shop\Contracts\ProductRepositoryInterface;
use Cartxis\Shop\Contracts\CategoryRepositoryInterface;
use Cartxis\Shop\Services\ShopService;
use Cartxis\CMS\Models\Block;

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
            $featuredCount = config('shop.homepage.featured_products_count', 12);

            // Primary hero identifier is configurable; fall back to existing demo identifiers.
            $primaryHeroIdentifier = (string) config('shop.homepage.hero_block_identifier', 'homepage-hero');
            $configuredHeroIdentifiers = config('shop.homepage.hero_block_identifiers');
            if (!is_array($configuredHeroIdentifiers)) {
                $configuredHeroIdentifiers = [];
            }

            $heroCandidates = array_values(array_unique(array_filter(array_merge(
                $configuredHeroIdentifiers,
                [
                    $primaryHeroIdentifier,
                    'homepage-hero',
                    'homepage-hero-2',
                    'fashion-hero-banner',
                    'fashion-hero-banner-2',
                ]
            ))));

            $blockIdentifiers = array_values(array_unique(array_filter(array_merge([
                'homepage-deal',
                'homepage-features',
                'homepage-testimonials',
                'homepage-brands',
            ], $heroCandidates))));

            // Version cache key by CMS block updates so homepage banners update immediately.
            $blocksUpdatedAt = Block::whereIn('identifier', $blockIdentifiers)->max('updated_at');
            $blocksVersion = $blocksUpdatedAt ? (int) strtotime((string) $blocksUpdatedAt) : 0;

            return $this->remember('homepage.data.v2.' . $blocksVersion, 3600, function () use ($featuredCount, $blockIdentifiers, $heroCandidates) {
                // Get active CMS blocks for homepage
                $blocks = Block::whereIn('identifier', $blockIdentifiers)
                    ->active()
                    ->scheduled()
                    ->get()
                    ->keyBy('identifier');

                $heroBlocks = [];
                foreach ($heroCandidates as $identifier) {
                    $block = $blocks->get($identifier);
                    if ($block) {
                        $heroBlocks[] = $block;
                    }
                }

                $heroBlock = $heroBlocks[0] ?? null;

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
                        'hero_slides' => !empty($heroBlocks) ? array_values(array_map(function ($block) {
                            return [
                                'id' => $block->id,
                                'title' => $block->title,
                                'content' => $block->content,
                                'type' => $block->type,
                                'data' => $block->type === 'banner' ? json_decode($block->content, true) : null,
                            ];
                        }, $heroBlocks)) : [],
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
