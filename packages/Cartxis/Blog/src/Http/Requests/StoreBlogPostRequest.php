<?php

declare(strict_types=1);

namespace Cartxis\Blog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255'],
            'excerpt'          => ['nullable', 'string', 'max:500'],
            'content'          => ['required', 'string'],
            'featured_image'   => ['nullable', 'string', 'max:500'],
            'status'           => ['required', 'in:draft,published,scheduled'],
            'published_at'     => ['nullable', 'date'],
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords'    => ['nullable', 'string', 'max:255'],
        ];
    }
}
