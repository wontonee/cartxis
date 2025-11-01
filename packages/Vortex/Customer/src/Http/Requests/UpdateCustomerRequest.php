<?php

declare(strict_types=1);

namespace Vortex\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        $customerId = $this->route('customer')->id;

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('customers', 'email')->ignore($customerId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'customer_group_id' => ['required', 'exists:customer_groups,id'],
            'company_name' => ['nullable', 'string', 'max:200'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'is_verified' => ['boolean'],
            'newsletter_subscribed' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'customer_group_id' => 'customer group',
            'date_of_birth' => 'date of birth',
            'company_name' => 'company name',
            'tax_id' => 'tax ID',
        ];
    }
}
