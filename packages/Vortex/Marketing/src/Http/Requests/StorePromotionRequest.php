<?php

namespace Vortex\Marketing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Vortex\Marketing\Models\Promotion;

class StorePromotionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', [
                Promotion::TYPE_CATALOG_RULE,
                Promotion::TYPE_CART_RULE,
                Promotion::TYPE_BUNDLE,
                Promotion::TYPE_FLASH_SALE,
                Promotion::TYPE_TIERED_PRICING,
            ]),
            'discount_type' => 'required|in:' . implode(',', [
                Promotion::DISCOUNT_PERCENTAGE,
                Promotion::DISCOUNT_FIXED_AMOUNT,
            ]),
            'discount_value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'stop_rules_processing' => 'boolean',
            'priority' => 'integer|min:0',
            'stackable' => 'boolean',
            'stackable_with_coupons' => 'boolean',
            'show_badge' => 'boolean',
            'badge_text' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'badge_bg_color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'badge_position' => 'nullable|in:' . implode(',', [
                Promotion::BADGE_TOP_LEFT,
                Promotion::BADGE_TOP_RIGHT,
                Promotion::BADGE_BOTTOM_LEFT,
                Promotion::BADGE_BOTTOM_RIGHT,
            ]),
            'show_countdown' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_per_customer' => 'nullable|integer|min:1',
            'conditions' => 'nullable|array',
            'actions' => 'nullable|array',
            'bundle_products' => 'nullable|array',
            'price_tiers' => 'nullable|array',
            'internal_notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'type' => 'promotion type',
            'discount_type' => 'discount type',
            'discount_value' => 'discount value',
            'max_discount' => 'maximum discount',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'usage_limit' => 'total usage limit',
            'usage_per_customer' => 'per customer usage limit',
            'badge_color' => 'badge text color',
            'badge_bg_color' => 'badge background color',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'End date must be equal to or after the start date.',
            'badge_color.regex' => 'Badge text color must be a valid hex color (e.g., #FF0000).',
            'badge_bg_color.regex' => 'Badge background color must be a valid hex color (e.g., #FF0000).',
        ];
    }
}
