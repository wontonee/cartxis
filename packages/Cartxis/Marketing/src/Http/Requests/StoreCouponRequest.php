<?php

namespace Cartxis\Marketing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Cartxis\Marketing\Models\Coupon;

class StoreCouponRequest extends FormRequest
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
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', [
                Coupon::TYPE_PERCENTAGE,
                Coupon::TYPE_FIXED_AMOUNT,
                Coupon::TYPE_FREE_SHIPPING,
                Coupon::TYPE_BUY_X_GET_Y,
                Coupon::TYPE_FIXED_PRICE,
            ]),
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'auto_apply' => 'boolean',
            'stackable' => 'boolean',
            'exclude_sale_items' => 'boolean',
            'priority' => 'integer|min:0',
            'usage_limit_total' => 'nullable|integer|min:1',
            'usage_limit_per_customer' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_restrictions' => 'nullable|array',
            'time_restrictions.start' => 'nullable|date_format:H:i',
            'time_restrictions.end' => 'nullable|date_format:H:i',
            'customer_groups' => 'nullable|array',
            'customer_groups.*' => 'integer',
            'first_order_only' => 'boolean',
            'min_account_age_days' => 'nullable|integer|min:0',
            'applicable_products' => 'nullable|array',
            'applicable_products.*' => 'integer',
            'applicable_categories' => 'nullable|array',
            'applicable_categories.*' => 'integer',
            'excluded_products' => 'nullable|array',
            'excluded_products.*' => 'integer',
            'excluded_categories' => 'nullable|array',
            'excluded_categories.*' => 'integer',
            'buy_quantity' => 'nullable|integer|min:1',
            'get_quantity' => 'nullable|integer|min:1',
            'buy_products' => 'nullable|array',
            'buy_products.*' => 'integer',
            'get_products' => 'nullable|array',
            'get_products.*' => 'integer',
            'internal_notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'code' => 'coupon code',
            'type' => 'discount type',
            'value' => 'discount value',
            'max_discount' => 'maximum discount',
            'min_order_amount' => 'minimum order amount',
            'usage_limit_total' => 'total usage limit',
            'usage_limit_per_customer' => 'per customer usage limit',
            'start_date' => 'start date',
            'end_date' => 'end date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'This coupon code is already taken.',
            'end_date.after_or_equal' => 'End date must be equal to or after the start date.',
        ];
    }
}
