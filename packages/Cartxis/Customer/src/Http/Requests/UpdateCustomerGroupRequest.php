<?php

declare(strict_types=1);

namespace Cartxis\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $groupId = $this->route('group')->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('customer_groups', 'code')->ignore($groupId),
                'regex:/^[a-z0-9-]+$/',
            ],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'auto_assignment_rules' => ['nullable', 'array'],
            'auto_assignment_rules.min_orders' => ['nullable', 'integer', 'min:0'],
            'auto_assignment_rules.min_spent' => ['nullable', 'numeric', 'min:0'],
            'auto_assignment_rules.min_aov' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'group name',
            'code' => 'group code',
            'discount_percentage' => 'discount percentage',
            'is_default' => 'default group',
            'auto_assignment_rules.min_orders' => 'minimum orders',
            'auto_assignment_rules.min_spent' => 'minimum spent amount',
            'auto_assignment_rules.min_aov' => 'minimum average order value',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.regex' => 'The group code must only contain lowercase letters, numbers, and dashes.',
            'color.regex' => 'The color must be a valid hex color code (e.g., #3B82F6).',
            'discount_percentage.max' => 'The discount percentage cannot exceed 100%.',
        ];
    }
}
