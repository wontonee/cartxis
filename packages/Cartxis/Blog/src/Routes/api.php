<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Cartxis\Blog\Models\BlogPost;
use Cartxis\Blog\Models\BlogCategory;

Route::group([
    'prefix' => 'api/blog',
    'middleware' => ['api', 'throttle:60,1'],
    'as' => 'api.blog.',
], function () {

    // Public: list published posts (used by BlogPostsGridBlock)
    Route::get('/posts', function (Request $request) {
        $limit      = min((int) $request->get('limit', 6), 50);
        $categoryId = $request->get('category_id');

        $posts = BlogPost::published()
            ->with(['category:id,name,slug'])
            ->when($categoryId, fn ($q) => $q->where('blog_category_id', $categoryId))
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'excerpt', 'featured_image', 'blog_category_id', 'published_at', 'author_name']);

        return response()->json(['data' => $posts]);
    })->name('posts.index');

    // Public: list active categories
    Route::get('/categories', function () {
        $categories = BlogCategory::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json(['data' => $categories]);
    })->name('categories.index');
});
