<?php

declare(strict_types=1);

namespace Cartxis\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Cartxis\CMS\Models\Page;
use Cartxis\CMS\Repositories\PageRepository;
use Cartxis\Core\Services\ThemeViewResolver;
use Cartxis\UIEditor\Services\LayoutService;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class PageController extends Controller
{
    public function __construct(
        protected PageRepository $pageRepository,
        protected ThemeViewResolver $themeResolver,
        protected LayoutService $layoutService
    ) {}

    /**
     * Display the specified page on the storefront.
     */
    public function show(string $slug): Response|RedirectResponse
    {
        // Drop legacy cache entries that stored serialized Eloquent models
        Cache::forget("page:{$slug}");

        // Cache scalar page data — never cache Eloquent models (unserialize fails across requests)
        $pageData = Cache::remember("page:data:{$slug}", 3600, function () use ($slug) {
            $page = $this->pageRepository->findByUrlKey($slug);

            if (! $page) {
                return null;
            }

            return [
                'id'               => $page->id,
                'title'            => $page->title,
                'content'          => $page->content,
                'meta_title'       => $page->meta_title ?? $page->title,
                'meta_description' => $page->meta_description,
                'meta_keywords'    => $page->meta_keywords,
                'url_key'          => $page->url_key,
                'status'           => $page->status,
                'is_homepage'      => (bool) $page->is_homepage,
                'created_at'       => $page->created_at?->format('F j, Y'),
                'updated_at'       => $page->updated_at?->format('F j, Y'),
            ];
        });

        if (! $pageData || $pageData['status'] !== 'published') {
            abort(404, 'Page not found');
        }

        // Homepage lives at / (HomeController). Redirect /home → /
        if ($pageData['is_homepage']) {
            return redirect('/');
        }

        $page = Page::find($pageData['id']);

        return Inertia::render($this->themeResolver->resolve('CMS/Page'), [
            'page' => [
                'id'               => $pageData['id'],
                'title'            => $pageData['title'],
                'content'          => $pageData['content'],
                'meta_title'       => $pageData['meta_title'],
                'meta_description' => $pageData['meta_description'],
                'meta_keywords'    => $pageData['meta_keywords'],
                'url_key'          => $pageData['url_key'],
                'created_at'       => $pageData['created_at'],
                'updated_at'       => $pageData['updated_at'],
            ],
            'layoutData' => $page ? $this->layoutService->getPublishedForPage($page)?->layout_data : null,
        ]);
    }
}
