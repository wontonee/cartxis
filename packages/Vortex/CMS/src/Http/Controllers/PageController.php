<?php

declare(strict_types=1);

namespace Vortex\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Vortex\CMS\Models\Page;
use Vortex\CMS\Repositories\PageRepository;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function __construct(
        protected PageRepository $pageRepository
    ) {}

    /**
     * Display the specified page on the storefront.
     */
    public function show(string $slug): Response
    {
        // Cache page for 1 hour
        $page = Cache::remember("page:{$slug}", 3600, function () use ($slug) {
            return $this->pageRepository->findByUrlKey($slug);
        });

        if (!$page || $page->status !== 'published') {
            abort(404, 'Page not found');
        }

        return Inertia::render('Page', [
            'page' => [
                'title' => $page->title,
                'content' => $page->content,
                'meta_title' => $page->meta_title ?? $page->title,
                'meta_description' => $page->meta_description,
                'meta_keywords' => $page->meta_keywords,
                'url_key' => $page->url_key,
                'created_at' => $page->created_at?->format('F j, Y'),
                'updated_at' => $page->updated_at?->format('F j, Y'),
            ],
        ]);
    }
}
