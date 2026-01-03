<?php

namespace Vortex\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Vortex\API\Helpers\ApiResponse;
use Vortex\API\Http\Resources\UserResource;
use Vortex\API\Http\Resources\AddressResource;
use Vortex\Customer\Models\Customer;
use Vortex\Customer\Models\CustomerAddress;

class CustomerController extends Controller
{
    /**
     * Get customer profile.
     */
    public function profile(Request $request)
    {
        $user = $request->user()->load(['addresses', 'orders']);

        return ApiResponse::success([
            'profile' => new UserResource($user),
            'statistics' => [
                'total_orders' => $user->orders->count(),
                'total_spent' => $user->orders->where('status', 'completed')->sum('grand_total'),
                'addresses_count' => $user->addresses->count(),
            ],
        ], 'Profile retrieved successfully');
    }

    /**
     * Update customer profile.
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        $user = $request->user();
        
        // Update only the fields that are present in the request
        $updateData = [];
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }
        if ($request->has('phone')) {
            $updateData['phone'] = $request->phone;
        }
        if ($request->has('date_of_birth')) {
            $updateData['date_of_birth'] = $request->date_of_birth;
        }
        if ($request->has('gender')) {
            $updateData['gender'] = $request->gender;
        }
        
        $user->update($updateData);
        $user->refresh();

        return ApiResponse::success(
            new UserResource($user),
            'Profile updated successfully'
        );
    }

    /**
     * Get customer addresses.
     */
    public function addresses(Request $request)
    {
        // Get customer for this user
        $customer = Customer::where('user_id', $request->user()->id)->first();
        
        if (!$customer) {
            return ApiResponse::success(
                [],
                'No addresses found'
            );
        }

        $addresses = CustomerAddress::where('customer_id', $customer->id)
            ->orderBy('is_default_shipping', 'desc')
            ->orderBy('is_default_billing', 'desc')
            ->get();

        return ApiResponse::success(
            AddressResource::collection($addresses),
            'Addresses retrieved successfully'
        );
    }

    /**
     * Create new address.
     */
    public function storeAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'label' => 'nullable|string|max:50',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:3',
            'postal_code' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'is_default' => 'nullable|boolean',
            'is_default_shipping' => 'nullable|boolean',
            'is_default_billing' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        // Get or create customer for this user
        $customer = Customer::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'email' => $request->user()->email,
                'first_name' => $request->user()->name,
                'last_name' => '',
                'is_active' => true,
            ]
        );

        // Determine if setting as default
        $isDefaultShipping = $request->is_default_shipping ?? $request->is_default ?? false;
        $isDefaultBilling = $request->is_default_billing ?? $request->is_default ?? false;

        // Use transaction to ensure atomicity
        $address = DB::transaction(function () use ($customer, $request, $isDefaultShipping, $isDefaultBilling) {
            // If setting as default, unset other defaults
            if ($isDefaultShipping) {
                CustomerAddress::where('customer_id', $customer->id)
                    ->update(['is_default_shipping' => false]);
            }
            
            if ($isDefaultBilling) {
                CustomerAddress::where('customer_id', $customer->id)
                    ->update(['is_default_billing' => false]);
            }

            return CustomerAddress::create([
                'customer_id' => $customer->id,
                'first_name' => $request->first_name ?? $request->user()->name,
                'last_name' => $request->last_name ?? '',
                'company' => $request->company,
                'label' => $request->label,
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'phone' => $request->phone,
                'is_default_shipping' => $isDefaultShipping,
                'is_default_billing' => $isDefaultBilling,
            ]);
        });

        return ApiResponse::success(
            new AddressResource($address),
            'Address created successfully',
            201
        );
    }

    /**
     * Update address.
     */
    public function updateAddress(Request $request, $id)
    {
        // Get customer for this user
        $customer = Customer::where('user_id', $request->user()->id)->first();
        
        if (!$customer) {
            return ApiResponse::notFound('Customer not found', 'CUSTOMER_NOT_FOUND');
        }

        $address = CustomerAddress::where('customer_id', $customer->id)
            ->where('id', $id)
            ->first();

        if (!$address) {
            return ApiResponse::notFound('Address not found', 'ADDRESS_NOT_FOUND');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'label' => 'nullable|string|max:50',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:3',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'is_default_shipping' => 'nullable|boolean',
            'is_default_billing' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator);
        }

        // Use transaction to ensure atomicity
        DB::transaction(function () use ($customer, $address, $request, $id) {
            // If setting as default, unset other defaults first
            if ($request->has('is_default_shipping') && $request->is_default_shipping) {
                CustomerAddress::where('customer_id', $customer->id)
                    ->where('id', '!=', $id)
                    ->update(['is_default_shipping' => false]);
            }

            if ($request->has('is_default_billing') && $request->is_default_billing) {
                CustomerAddress::where('customer_id', $customer->id)
                    ->where('id', '!=', $id)
                    ->update(['is_default_billing' => false]);
            }

            $address->update($request->all());
        });

        return ApiResponse::success(
            new AddressResource($address),
            'Address updated successfully'
        );
    }

    /**
     * Delete address.
     */
    public function deleteAddress(Request $request, $id)
    {
        // Get customer for this user
        $customer = Customer::where('user_id', $request->user()->id)->first();
        
        if (!$customer) {
            return ApiResponse::notFound('Customer not found', 'CUSTOMER_NOT_FOUND');
        }

        $address = CustomerAddress::where('customer_id', $customer->id)
            ->where('id', $id)
            ->first();

        if (!$address) {
            return ApiResponse::notFound('Address not found', 'ADDRESS_NOT_FOUND');
        }

        $address->delete();

        return ApiResponse::success(null, 'Address deleted successfully');
    }
}
