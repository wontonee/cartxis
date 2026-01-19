<?php

namespace Cartxis\Stripe\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation request for Stripe payment gateway configuration
 * This is extension-specific validation, separate from core Settings validation
 */
class SaveStripeConfigRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            
            // Stripe API Keys - no max length restriction for flexibility
            'configuration.secret_key' => ['required', 'string', 'regex:/^sk_(test|live)_/'],
            'configuration.public_key' => ['required', 'string', 'regex:/^pk_(test|live)_/'],
            'configuration.webhook_secret' => ['nullable', 'string', 'regex:/^whsec_/'],
            
            // Stripe Features
            'configuration.enable_3d_secure' => ['nullable', 'boolean'],
            'configuration.save_payment_method' => ['nullable', 'boolean'],
            
            // Payment Methods
            'configuration.payment_methods' => ['nullable', 'array'],
            'configuration.payment_methods.card' => ['nullable', 'boolean'],
            'configuration.payment_methods.apple_pay' => ['nullable', 'boolean'],
            'configuration.payment_methods.google_pay' => ['nullable', 'boolean'],
            'configuration.payment_methods.ideal' => ['nullable', 'boolean'],
            'configuration.payment_methods.bancontact' => ['nullable', 'boolean'],
            'configuration.payment_methods.eps' => ['nullable', 'boolean'],
            'configuration.payment_methods.giropay' => ['nullable', 'boolean'],
            'configuration.payment_methods.klarna' => ['nullable', 'boolean'],
            'configuration.payment_methods.p24' => ['nullable', 'boolean'],
            'configuration.payment_methods.alipay' => ['nullable', 'boolean'],
            'configuration.payment_methods.wechat_pay' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validation errors
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Payment method name is required',
            'configuration.secret_key.required' => 'Stripe Secret Key is required',
            'configuration.secret_key.regex' => 'Secret Key must start with sk_test_ or sk_live_',
            'configuration.public_key.required' => 'Stripe Publishable Key is required',
            'configuration.public_key.regex' => 'Publishable Key must start with pk_test_ or pk_live_',
            'configuration.webhook_secret.regex' => 'Webhook Secret must start with whsec_',
        ];
    }

    /**
     * Get custom attribute names for error messages
     */
    public function attributes(): array
    {
        return [
            'configuration.secret_key' => 'secret key',
            'configuration.public_key' => 'publishable key',
            'configuration.webhook_secret' => 'webhook secret',
        ];
    }
}
