<?php

declare(strict_types=1);

namespace Vortex\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceSettingsRequest extends FormRequest
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
            'show_eta' => 'nullable|boolean',
        ];
    }
}
