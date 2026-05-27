<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CustomerAuthService
{
    public function currentCustomer(Request $request): ?array
    {
        if (! $request->hasSession()) {
            return null;
        }

        $customerId = $request->session()->get('customer_id');

        if (! $customerId) {
            return null;
        }

        $customer = Customer::query()
            ->whereKey($customerId)
            ->where('status', true)
            ->first();

        if (! $customer) {
            $request->session()->forget('customer_id');

            return null;
        }

        return $this->formatCustomer($customer);
    }

    /**
     * @throws ValidationException
     */
    public function register(array $input, Request $request): array
    {
        $input = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validate();

        $customer = Customer::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'status' => true,
        ]);

        if ($request->hasSession()) {
            $request->session()->forget('customer_id');
        }

        return [
            'customer' => $this->formatCustomer($customer),
        ];
    }

    /**
     * @throws ValidationException
     */
    public function login(array $input, Request $request): array
    {
        $input = Validator::make($input, [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ])->validate();

        $customer = Customer::query()
            ->where('email', $input['email'])
            ->where('status', true)
            ->first();

        if (! $customer || ! $customer->password || ! Hash::check($input['password'], $customer->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        if (! $request->hasSession()) {
            throw ValidationException::withMessages([
                'email' => 'Customer login is unavailable because sessions are not enabled for this request.',
            ]);
        }

        $request->session()->put('customer_id', $customer->customer_id);

        return [
            'customer' => $this->formatCustomer($customer),
        ];
    }

    public function logout(Request $request): bool
    {
        if ($request->hasSession()) {
            $request->session()->forget('customer_id');
        }

        return true;
    }

    private function formatCustomer(Customer $customer): array
    {
        return [
            'customer_id' => $customer->customer_id,
            'name' => $customer->name,
            'email' => $customer->email,
        ];
    }
}
