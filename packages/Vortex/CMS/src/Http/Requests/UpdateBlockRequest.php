<?php

declare(strict_types=1);

namespace Vortex\CMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBlockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $blockId = $this->route('block');

        return [
            'identifier' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('blocks', 'identifier')->ignore($blockId),
            ],
            'title' => 'required|string|max:255',
            'type' => 'required|in:text,html,banner,promotion,newsletter',
            'content' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'channel_id' => 'nullable|exists:channels,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'identifier.required' => 'Block identifier is required',
            'identifier.regex' => 'Block identifier must contain only lowercase letters, numbers, and hyphens',
            'identifier.unique' => 'This block identifier is already in use',
            'title.required' => 'Block title is required',
            'type.required' => 'Block type is required',
            'type.in' => 'Invalid block type selected',
            'status.required' => 'Status is required',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
        ];
    }
}
