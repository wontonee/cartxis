<?php

declare(strict_types=1);

namespace Cartxis\Blog\Http\Controllers\Admin;

use Cartxis\Blog\Models\BlogCategory;
use Cartxis\Blog\Http\Requests\StoreBlogCategoryRequest;
use Cartxis\Blog\Http\Requests\UpdateBlogCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogCategoriesController
{
    public function index(Request $request): Response
    {
        $query = BlogCategory::withCount('posts')
            ->orderBy($request->input('sort_by', 'name'), $request->input('sort_order', 'asc'));

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $categories = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Blog/Categories/Index', [
            'categories' => $categories,
            'filters'    => $request->only(['search', 'status']),
            'statistics' => [
                'total'      => BlogCategory::count(),
                'active'     => BlogCategory::where('status', 'active')->count(),
                'inactive'   => BlogCategory::where('status', 'inactive')->count(),
                'with_posts' => BlogCategory::has('posts')->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Blog/Categories/Create');
    }

    public function store(StoreBlogCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category = BlogCategory::create($data);

        return redirect()
            ->route('admin.blog.categories.edit', $category)
            ->with('success', 'Category created successfully.');
    }

    public function edit(BlogCategory $category): Response
    {
        return Inertia::render('Admin/Blog/Categories/Edit', [
            'category'  => $category->loadCount('posts'),
        ]);
    }

    public function update(UpdateBlogCategoryRequest $request, BlogCategory $category): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(BlogCategory $category): RedirectResponse
    {
        if ($category->posts()->exists()) {
            return back()->with('error', 'Cannot delete a category that has posts. Reassign posts first.');
        }

        $category->delete();

        return redirect()
            ->route('admin.blog.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
