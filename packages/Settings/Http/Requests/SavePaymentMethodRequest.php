<?php

namespace Vortex\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePaymentMethodRequest extends FormRequest
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
        $type = $this->route('type');

        if ($type === 'cod') {
            return $this->codRules();
        } elseif ($type === 'bank_transfer') {
            return $this->bankTransferRules();
        } elseif ($type === 'stripe') {
            return $this->stripeRules();
        }

        return [];
    }

    /**
     * Validation rules for Cash on Delivery method
     */
    private function codRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'max_order_amount' => ['required', 'numeric', 'min:0'],
            'min_order_amount_currency' => ['required', 'string', 'size:3'],
            'handling_fee' => ['required', 'numeric', 'min:0'],
            'handling_fee_type' => ['required', 'in:fixed,percentage'],
            'handling_fee_minimum' => ['required', 'numeric', 'min:0'],
            'handling_fee_maximum' => ['required', 'numeric', 'min:0'],
            'enabled_countries' => ['nullable', 'array'],
            'enabled_countries.*' => ['string', 'size:2'],
            'enabled_regions' => ['nullable', 'array'],
            'enabled_regions.*' => ['string'],
        ];
    }

    /**
     * Validation rules for Bank Transfer method
     */
    private function bankTransferRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_holder_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'bank_code' => ['nullable', 'string', 'max:50'],
            'iban' => ['nullable', 'string', 'max:34'],
            'swift_code' => ['nullable', 'string', 'max:11'],
            'payment_reference_format' => ['required', 'string', 'max:255'],
            'verification_required' => ['required', 'boolean'],
            'verification_days' => ['required', 'integer', 'min:1', 'max:90'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'max_order_amount' => ['required', 'numeric', 'min:0'],
            'min_order_amount_currency' => ['required', 'string', 'size:3'],
            'handling_fee' => ['required', 'numeric', 'min:0'],
            'handling_fee_type' => ['required', 'in:fixed,percentage'],
            'handling_fee_maximum' => ['required', 'numeric', 'min:0'],
            'enabled_countries' => ['nullable', 'array'],
            'enabled_countries.*' => ['string', 'size:2'],
        ];
    }

    /**
     * Validation rules for Stripe payment method
     */
    private function stripeRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'configuration.public_key' => ['required', 'string', 'max:255'],
            'configuration.enable_3d_secure' => ['boolean'],
            'configuration.save_payment_method' => ['boolean'],
            'configuration.payment_methods' => ['array'],
            'configuration.payment_methods.card' => ['boolean'],
            'configuration.payment_methods.apple_pay' => ['boolean'],
            'configuration.payment_methods.google_pay' => ['boolean'],
            'configuration.payment_methods.ideal' => ['boolean'],
            'configuration.payment_methods.bancontact' => ['boolean'],
            'configuration.payment_methods.eps' => ['boolean'],
            'configuration.payment_methods.giropay' => ['boolean'],
            'configuration.payment_methods.klarna' => ['boolean'],
            'configuration.payment_methods.p24' => ['boolean'],
            'configuration.payment_methods.alipay' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validation errors
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Payment method name is required',
            'name.max' => 'Payment method name cannot exceed 255 characters',
            'bank_name.required' => 'Bank name is required for bank transfer',
            'account_holder_name.required' => 'Account holder name is required',
            'account_number.required' => 'Account number is required',
            'min_order_amount.numeric' => 'Minimum order amount must be a valid number',
            'max_order_amount.numeric' => 'Maximum order amount must be a valid number',
            'handling_fee.numeric' => 'Handling fee must be a valid number',
            'handling_fee_type.in' => 'Handling fee type must be either fixed or percentage',
            'verification_days.min' => 'Verification days must be at least 1',
            'verification_days.max' => 'Verification days cannot exceed 90',
            'iban.string' => 'IBAN must be a valid string',
            'swift_code.string' => 'SWIFT code must be a valid string',
            'configuration.public_key.required' => 'Stripe Publishable Key is required',
        ];
    }
}
