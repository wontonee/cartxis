<?php

declare(strict_types=1);

namespace Vortex\Customer\Http\Resources;

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
                return [
                    'id' => $this->customerGroup->id,
                    'name' => $this->customerGroup->name,
                    'code' => $this->customerGroup->code,
                    'color' => $this->customerGroup->color,
                    'discount_percentage' => $this->customerGroup->discount_percentage,
                ];
            }),
            'company_name' => $this->company_name,
            'tax_id' => $this->tax_id,
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'newsletter_subscribed' => $this->newsletter_subscribed,
            'total_orders' => $this->total_orders,
            'total_spent' => (float) $this->total_spent,
            'average_order_value' => (float) $this->average_order_value,
            'last_order_date' => $this->last_order_date?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'addresses' => $this->whenLoaded('addresses', function () {
                return CustomerAddressResource::collection($this->addresses);
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
