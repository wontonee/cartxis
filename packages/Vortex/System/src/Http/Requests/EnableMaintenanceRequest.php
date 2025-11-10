<?php

declare(strict_types=1);

namespace Vortex\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnableMaintenanceRequest extends FormRequest
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
        return [
            'title' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:1000',
            'retry_after' => 'nullable|integer|min:60|max:86400',
            'allowed_ips' => 'nullable|array',
            'allowed_ips.*' => 'ip',
            'bypass_admin' => 'nullable|boolean',
            'contact_email' => 'nullable|email|max:255',
            'secret' => 'nullable|string|min:16|max:64',
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.max' => 'The title may not be greater than 255 characters.',
            'message.max' => 'The message may not be greater than 1000 characters.',
            'retry_after.min' => 'Retry after must be at least 60 seconds.',
            'retry_after.max' => 'Retry after may not be greater than 86400 seconds (24 hours).',
            'allowed_ips.*.ip' => 'Each IP address must be a valid IP address.',
            'contact_email.email' => 'The contact email must be a valid email address.',
            'secret.min' => 'The secret token must be at least 16 characters.',
        ];
    }
}
