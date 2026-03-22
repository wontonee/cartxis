<?php

declare(strict_types=1);

namespace Cartxis\Blog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255', 'unique:blog_categories,slug'],
            'description'      => ['nullable', 'string'],
            'status'           => ['required', 'in:active,inactive'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
