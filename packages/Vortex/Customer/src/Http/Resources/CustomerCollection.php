<?php

declare(strict_types=1);

namespace Vortex\Customer\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                    'full_name' => $customer->full_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'customer_group' => [
                        'id' => $customer->customerGroup->id,
                        'name' => $customer->customerGroup->name,
                        'color' => $customer->customerGroup->color,
                    ],
                    'company_name' => $customer->company_name,
                    'is_active' => $customer->is_active,
                    'is_verified' => $customer->is_verified,
                    'newsletter_subscribed' => $customer->newsletter_subscribed,
                    'total_orders' => $customer->total_orders,
                    'total_spent' => (float) $customer->total_spent,
                    'created_at' => $customer->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'links' => $this->resource->linkCollection()->toArray(),
            'meta' => [
                'current_page' => $this->resource->currentPage(),
                'from' => $this->resource->firstItem(),
                'last_page' => $this->resource->lastPage(),
                'per_page' => $this->resource->perPage(),
                'to' => $this->resource->lastItem(),
                'total' => $this->resource->total(),
            ],
        ];
    }
}
