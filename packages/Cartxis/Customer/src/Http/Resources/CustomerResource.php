<?php

declare(strict_types=1);

namespace Cartxis\Customer\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'customer_group_id' => $this->customer_group_id,
            'customer_group' => $this->whenLoaded('customerGroup', function () {
                return $this->customerGroup ? [
                    'id' => $this->customerGroup->id,
                    'name' => $this->customerGroup->name,
                    'code' => $this->customerGroup->code,
                    'color' => $this->customerGroup->color,
                    'discount_percentage' => $this->customerGroup->discount_percentage,
                ] : [
                    'id' => null,
                    'name' => 'No Group',
                    'code' => 'no-group',
                    'color' => '#6B7280',
                    'discount_percentage' => 0,
                ];
            }),
            'company_name' => $this->company_name,
            'tax_id' => $this->tax_id,
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'is_guest' => $this->is_guest,
            'newsletter_subscribed' => $this->newsletter_subscribed,
            'total_orders' => $this->total_orders,
            'total_spent' => (float) $this->total_spent,
            'average_order_value' => (float) $this->average_order_value,
            'last_order_date' => $this->last_order_date?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'addresses' => $this->whenLoaded('addresses', function () {
                return $this->addresses->map(function ($address) {
                    return [
                        'id' => $address->id,
                        'customer_id' => $address->customer_id,
                        'type' => $address->type,
                        'first_name' => $address->first_name,
                        'last_name' => $address->last_name,
                        'full_name' => $address->full_name,
                        'company' => $address->company,
                        'address_line_1' => $address->address_line_1,
                        'address_line_2' => $address->address_line_2,
                        'city' => $address->city,
                        'state' => $address->state,
                        'postal_code' => $address->postal_code,
                        'country' => $address->country,
                        'phone' => $address->phone,
                        'is_default_shipping' => $address->is_default_shipping,
                        'is_default_billing' => $address->is_default_billing,
                        'formatted_address' => $address->formatted_address,
                        'created_at' => $address->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $address->updated_at->format('Y-m-d H:i:s'),
                    ];
                });
            }),
            'customer_notes' => $this->whenLoaded('notes', function () {
                return $this->notes->map(function ($note) {
                    return [
                        'id' => $note->id,
                        'note' => $note->note,
                        'is_visible_to_customer' => $note->is_visible_to_customer,
                        'created_at' => $note->created_at->format('Y-m-d H:i:s'),
                        'user' => $note->user ? [
                            'id' => $note->user->id,
                            'name' => $note->user->name,
                        ] : null,
                    ];
                });
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }
}
