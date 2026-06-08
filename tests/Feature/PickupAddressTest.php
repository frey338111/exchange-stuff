<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\PickupAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PickupAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_customer_can_manage_pickup_addresses(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'status' => true,
        ]);

        $this
            ->withSession(['customer_id' => $customer->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    mutation AddAddress($input: AddAddressInput!) {
                        addAddress(input: $input) {
                            success
                            message
                            address {
                                pickup_address_id
                                address_line_1
                                country
                            }
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'input' => [
                        'name' => 'Collection Point',
                        'phone' => '07123456789',
                        'address_line_1' => '10 Market Street',
                        'city' => 'Bristol',
                        'postcode' => 'BS1 1AA',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.addAddress.success', true)
            ->assertJsonPath('data.addAddress.message', 'Pickup address added.')
            ->assertJsonPath('data.addAddress.address.address_line_1', '10 Market Street')
            ->assertJsonPath('data.addAddress.address.country', 'GB');

        $address = PickupAddress::firstOrFail();

        $this
            ->withSession(['customer_id' => $customer->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    {
                        getMyAddress {
                            pickup_address_id
                            address_line_1
                        }
                    }
                GRAPHQL,
            ])
            ->assertOk()
            ->assertJsonCount(1, 'data.getMyAddress')
            ->assertJsonPath('data.getMyAddress.0.address_line_1', '10 Market Street');

        $this
            ->withSession(['customer_id' => $customer->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    mutation AddAddress($input: AddAddressInput!) {
                        addAddress(input: $input) {
                            success
                            message
                            address {
                                pickup_address_id
                                address_line_1
                            }
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'input' => [
                        'pickup_address_id' => $address->pickup_address_id,
                        'name' => 'Updated Collection Point',
                        'address_line_1' => '20 Market Street',
                        'city' => 'Bristol',
                        'postcode' => 'BS1 1AB',
                        'country' => 'GB',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.addAddress.success', true)
            ->assertJsonPath('data.addAddress.message', 'Pickup address updated.')
            ->assertJsonPath('data.addAddress.address.address_line_1', '20 Market Street');

        $this->assertDatabaseHas('pickup_address', [
            'pickup_address_id' => $address->pickup_address_id,
            'address_line_1' => '20 Market Street',
            'country' => 'GB',
        ]);

        $this
            ->withSession(['customer_id' => $customer->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    mutation DeleteAddress($pickup_address_id: Int!) {
                        deleteAddress(pickup_address_id: $pickup_address_id) {
                            success
                            message
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'pickup_address_id' => $address->pickup_address_id,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.deleteAddress.success', true)
            ->assertJsonPath('data.deleteAddress.message', 'Pickup address deleted.');

        $this->assertDatabaseMissing('pickup_address', [
            'pickup_address_id' => $address->pickup_address_id,
        ]);
    }

    public function test_customer_cannot_update_or_delete_another_customers_pickup_address(): void
    {
        $owner = Customer::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'status' => true,
        ]);
        $otherCustomer = Customer::create([
            'name' => 'Other Customer',
            'email' => 'other@example.com',
            'status' => true,
        ]);
        $address = PickupAddress::create([
            'customer_id' => $owner->customer_id,
            'address_line_1' => '10 Market Street',
            'city' => 'Bristol',
            'postcode' => 'BS1 1AA',
            'country' => 'GB',
        ]);

        $this
            ->withSession(['customer_id' => $otherCustomer->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    mutation AddAddress($input: AddAddressInput!) {
                        addAddress(input: $input) {
                            success
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'input' => [
                        'pickup_address_id' => $address->pickup_address_id,
                        'address_line_1' => '20 Market Street',
                        'city' => 'Bristol',
                        'postcode' => 'BS1 1AB',
                        'country' => 'GB',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.addAddress', null);

        $this
            ->withSession(['customer_id' => $otherCustomer->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    mutation DeleteAddress($pickup_address_id: Int!) {
                        deleteAddress(pickup_address_id: $pickup_address_id) {
                            success
                        }
                    }
                GRAPHQL,
                'variables' => [
                    'pickup_address_id' => $address->pickup_address_id,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.deleteAddress', null);

        $this->assertDatabaseHas('pickup_address', [
            'pickup_address_id' => $address->pickup_address_id,
            'address_line_1' => '10 Market Street',
        ]);
    }
}
