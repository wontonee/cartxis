<?php

declare(strict_types=1);

namespace Cartxis\Customer\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'color' => $this->color,
            'discount_percentage' => (float) $this->discount_percentage,
            'order' => $this->order,
            'is_default' => $this->is_default,
            'auto_assignment_rules' => $this->auto_assignment_rules ?? [],
            'status' => $this->status,
            'customers_count' => $this->when(isset($this->customers_count), $this->customers_count ?? 0),
            'active_customers_count' => $this->when(isset($this->active_customers_count), $this->active_customers_count ?? 0),
            'customers' => $this->whenLoaded('customers', function () {
                return $this->customers->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'first_name' => $customer->first_name,
                        'last_name' => $customer->last_name,
                        'full_name' => $customer->full_name,
                        'email' => $customer->email,
                    ];
                });
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
