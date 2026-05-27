<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registration_does_not_log_the_customer_in(): void
    {
        $response = $this->withSession(['customer_id' => 999])->postJson('/graphql', [
            'query' => <<<'GRAPHQL'
                mutation RegisterCustomer($input: RegisterCustomerInput!) {
                    registerCustomer(input: $input) {
                        customer {
                            customer_id
                            email
                        }
                    }
                }
            GRAPHQL,
            'variables' => [
                'input' => [
                    'name' => 'Test Customer',
                    'email' => 'customer@example.com',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.registerCustomer.customer.email', 'customer@example.com')
            ->assertSessionMissing('customer_id');
    }

    public function test_customer_login_cannot_access_dashboard(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'status' => true,
        ]);

        $this
            ->withSession(['customer_id' => $customer->customer_id])
            ->get('/dashboard')
            ->assertRedirect('/');
    }

    public function test_current_customer_returns_customer_from_session(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'status' => true,
        ]);

        $this
            ->withSession(['customer_id' => $customer->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    {
                        currentCustomer {
                            customer_id
                            name
                            email
                        }
                    }
                GRAPHQL,
            ])
            ->assertOk()
            ->assertJsonPath('data.currentCustomer.email', 'customer@example.com');
    }

    public function test_customer_logout_clears_customer_session(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'status' => true,
        ]);

        $this
            ->withSession(['customer_id' => $customer->customer_id])
            ->postJson('/graphql', [
                'query' => <<<'GRAPHQL'
                    mutation {
                        logoutCustomer
                    }
                GRAPHQL,
            ])
            ->assertOk()
            ->assertJsonPath('data.logoutCustomer', true)
            ->assertSessionMissing('customer_id');
    }
}
