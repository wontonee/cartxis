<?php

namespace Cartxis\API\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Support both CustomerAddress (address_line_1) and Address model (address_line1)
        $line1 = $this->address_line_1 ?? $this->address_line1;
        $line2 = $this->address_line_2 ?? $this->address_line2;

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => trim("{$this->first_name} {$this->last_name}"),
            'company' => $this->company,
            'label' => $this->label ?? null,
            'address_line_1' => $line1,
            'address_line_2' => $line2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'phone' => $this->phone,
            'email' => $this->email ?? null,
            'is_default_shipping' => (bool) ($this->is_default_shipping ?? ($this->is_default ?? false)),
            'is_default_billing' => (bool) ($this->is_default_billing ?? false),
            'formatted_address' => $this->getFormattedAddress($line1, $line2),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Get formatted address string.
     *
     * @return string
     */
    private function getFormattedAddress(?string $line1, ?string $line2): string
    {
        $parts = array_filter([
            $line1,
            $line2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }
}
