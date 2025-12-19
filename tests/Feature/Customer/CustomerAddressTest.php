<?php

declare(strict_types=1);

namespace Tests\Feature\Customer;

use Tests\TestCase;
use Vortex\Customer\Models\Customer;
use Vortex\Customer\Models\CustomerAddress;
use Vortex\Customer\Models\CustomerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerAddressTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a customer group
        $group = CustomerGroup::create([
            'name' => 'Test Group',
            'code' => 'test-group',
            'description' => 'Test Description',
            'color' => '#000000',
            'is_active' => true,
            'order' => 1,
        ]);

        // Create a customer
        $this->customer = Customer::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'customer_group_id' => $group->id,
            'is_active' => true,
            'is_verified' => true,
        ]);
    }

    public function test_can_list_customer_addresses(): void
    {
        // Create some addresses
        CustomerAddress::create([
            'customer_id' => $this->customer->id,
            'type' => 'shipping',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
            'is_default_shipping' => true,
        ]);

        $response = $this->actingAs($this->getAdminUser(), 'admin')
            ->get(route('admin.customers.addresses.index', $this->customer));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/Customer/Addresses/Index')
                ->has('customer')
                ->has('addresses.data', 1)
        );
    }

    public function test_can_create_customer_address(): void
    {
        $addressData = [
            'type' => 'billing',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'address_line_1' => '456 Oak Ave',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'postal_code' => '90001',
            'country' => 'US',
            'phone' => '555-1234',
            'is_default_billing' => true,
        ];

        $response = $this->actingAs($this->getAdminUser(), 'admin')
            ->post(route('admin.customers.addresses.store', $this->customer), $addressData);

        $response->assertRedirect(route('admin.customers.addresses.index', $this->customer));
        $response->assertSessionHas('success', 'Address added successfully.');

        $this->assertDatabaseHas('customer_addresses', [
            'customer_id' => $this->customer->id,
            'first_name' => 'Jane',
            'address_line_1' => '456 Oak Ave',
        ]);
    }

    public function test_can_update_customer_address(): void
    {
        $address = CustomerAddress::create([
            'customer_id' => $this->customer->id,
            'type' => 'shipping',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
        ]);

        $updateData = [
            'type' => 'shipping',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '789 Updated St',
            'city' => 'Boston',
            'state' => 'MA',
            'postal_code' => '02101',
            'country' => 'US',
        ];

        $response = $this->actingAs($this->getAdminUser(), 'admin')
            ->put(route('admin.customers.addresses.update', [$this->customer, $address]), $updateData);

        $response->assertRedirect(route('admin.customers.addresses.index', $this->customer));
        $response->assertSessionHas('success', 'Address updated successfully.');

        $this->assertDatabaseHas('customer_addresses', [
            'id' => $address->id,
            'address_line_1' => '789 Updated St',
            'city' => 'Boston',
        ]);
    }

    public function test_can_delete_customer_address(): void
    {
        $address = CustomerAddress::create([
            'customer_id' => $this->customer->id,
            'type' => 'shipping',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
        ]);

        $response = $this->actingAs($this->getAdminUser(), 'admin')
            ->delete(route('admin.customers.addresses.destroy', [$this->customer, $address]));

        $response->assertRedirect(route('admin.customers.addresses.index', $this->customer));
        $response->assertSessionHas('success', 'Address deleted successfully.');

        $this->assertDatabaseMissing('customer_addresses', [
            'id' => $address->id,
        ]);
    }

    public function test_can_set_default_shipping_address(): void
    {
        $address = CustomerAddress::create([
            'customer_id' => $this->customer->id,
            'type' => 'shipping',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
            'is_default_shipping' => false,
        ]);

        $response = $this->actingAs($this->getAdminUser(), 'admin')
            ->post(route('admin.customers.addresses.set-default-shipping', [$this->customer, $address]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Default shipping address set.');

        $this->assertDatabaseHas('customer_addresses', [
            'id' => $address->id,
            'is_default_shipping' => true,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->actingAs($this->getAdminUser(), 'admin')
            ->post(route('admin.customers.addresses.store', $this->customer), []);

        $response->assertSessionHasErrors([
            'type',
            'first_name',
            'last_name',
            'address_line_1',
            'city',
            'state',
            'postal_code',
            'country',
        ]);
    }

    public function test_validates_country_code_length(): void
    {
        $addressData = [
            'type' => 'shipping',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA', // Should be 2 chars
        ];

        $response = $this->actingAs($this->getAdminUser(), 'admin')
            ->post(route('admin.customers.addresses.store', $this->customer), $addressData);

        $response->assertSessionHasErrors(['country']);
    }

    protected function getAdminUser()
    {
        // Create a simple user for admin auth
        return \App\Models\User::factory()->create([
            'email' => 'admin@test.com',
        ]);
    }
}
