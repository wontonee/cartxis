<?php

declare(strict_types=1);

namespace Cartxis\Blog\Http\Controllers\Admin;

use Cartxis\Blog\Models\BlogCategory;
use Cartxis\Blog\Models\BlogPost;
use Cartxis\Blog\Services\BlogService;
use Cartxis\Blog\Http\Requests\StoreBlogPostRequest;
use Cartxis\Blog\Http\Requests\UpdateBlogPostRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogPostsController
{
    public function __construct(
        protected BlogService $blogService
    ) {}

    public function index(Request $request): Response
    {
        $query = BlogPost::with(['category', 'creator'])
            ->orderBy(
                $request->input('sort_by', 'created_at'),
                $request->input('sort_order', 'desc')
            );

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('blog_category_id', $categoryId);
        }

        $posts = $query->paginate(15)->withQueryString();

        $statistics = [
            'total'     => BlogPost::count(),
            'published' => BlogPost::where('status', 'published')->count(),
            'draft'     => BlogPost::where('status', 'draft')->count(),
            'scheduled' => BlogPost::where('status', 'scheduled')->count(),
        ];

        $categories = BlogCategory::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/Blog/Index', [
            'posts'      => $posts,
            'statistics' => $statistics,
            'categories' => $categories,
            'filters'    => $request->only(['search', 'status', 'category_id', 'sort_by', 'sort_order']),
        ]);
    }

    public function create(): Response
    {
        $categories = BlogCategory::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/Blog/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreBlogPostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['author_id']  = auth()->id();

        $post = $this->blogService->create($data);

        return redirect()
            ->route('admin.blog.edit', $post)
            ->with('success', 'Blog post created successfully.');
    }

    public function edit(BlogPost $post): Response
    {
        $categories = BlogCategory::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Admin/Blog/Edit', [
            'post'       => $post->load(['category', 'creator']),
            'categories' => $categories,
        ]);
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $post): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $this->blogService->update($post, $data);

        return back()->with('success', 'Blog post updated successfully.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        try {
            $this->blogService->delete($post);
            return redirect()
                ->route('admin.blog.index')
                ->with('success', 'Blog post deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete post: ' . $e->getMessage());
        }
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:publish,draft,delete',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'exists:blog_posts,id',
        ]);

        try {
            $count = match ($validated['action']) {
                'publish' => $this->blogService->bulkUpdateStatus($validated['ids'], 'published'),
                'draft'   => $this->blogService->bulkUpdateStatus($validated['ids'], 'draft'),
                'delete'  => $this->blogService->bulkDelete($validated['ids']),
            };

            $action = ucfirst($validated['action']);
            return back()->with('success', "{$action} completed on {$count} post(s).");
        } catch (\Exception $e) {
            return back()->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }

    public function checkSlug(Request $request)
    {
        $slug      = $request->input('slug');
        $excludeId = $request->input('exclude_id');

        if (empty($slug)) {
            return response()->json(['available' => false, 'error' => 'Slug cannot be empty'], 400);
        }

        return response()->json([
            'available' => ! $this->blogService->slugExists($slug, $excludeId),
            'slug'      => $slug,
        ]);
    }
}
