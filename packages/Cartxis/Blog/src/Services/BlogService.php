<?php

declare(strict_types=1);

namespace Cartxis\Blog\Services;

use Cartxis\Blog\Models\BlogPost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class BlogService
{
    public function create(array $data): BlogPost
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = $this->generateUniqueSlug(
                $data['title'],
                $data['slug'] ?? null
            );

            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            if (isset($data['content'])) {
                $data['content'] = Purifier::clean($data['content']);
            }

            return BlogPost::create($data);
        });
    }

    public function update(BlogPost $post, array $data): BlogPost
    {
        DB::transaction(function () use ($post, $data) {
            if (isset($data['title']) && $data['title'] !== $post->title) {
                $data['slug'] = $this->generateUniqueSlug(
                    $data['title'],
                    $data['slug'] ?? null,
                    $post->id
                );
            }

            if ($data['status'] === 'published' && $post->status !== 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            if (isset($data['content'])) {
                $data['content'] = Purifier::clean($data['content']);
            }

            $post->update($data);
            $this->clearPostCache($post->slug);
        });

        return $post->fresh();
    }

    public function delete(BlogPost $post): bool
    {
        $this->clearPostCache($post->slug);
        return $post->delete();
    }

    public function generateUniqueSlug(
        string $title,
        ?string $preferredSlug = null,
        ?int $excludeId = null
    ): string {
        $slug = $preferredSlug ?? Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = BlogPost::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    protected function clearPostCache(string $slug): void
    {
        Cache::forget("blog_post:{$slug}");
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return BlogPost::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function bulkDelete(array $ids): int
    {
        $posts = BlogPost::whereIn('id', $ids)->get();

        foreach ($posts as $post) {
            $this->clearPostCache($post->slug);
        }

        return BlogPost::whereIn('id', $ids)->delete();
    }
}
