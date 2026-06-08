<?php

namespace App\GraphQL\Mutations;

use App\Models\PickupAddress;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AddAddress
{
    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root, array $args): array
    {
        $request = request();

        if (! $request->hasSession() || ! $request->session()->has('customer_id')) {
            throw ValidationException::withMessages([
                'customer' => 'You must be logged in to save a pickup address.',
            ]);
        }

        $customerId = (int) $request->session()->get('customer_id');

        $input = Validator::make($args['input'], [
            'pickup_address_id' => ['nullable', 'integer', 'exists:pickup_address,pickup_address_id'],
            'name' => ['nullable', 'string', 'max:255', 'not_regex:/<[^>]*>/'],
            'phone' => ['nullable', 'string', 'max:50', 'not_regex:/<[^>]*>/'],
            'address_line_1' => ['required', 'string', 'max:255', 'not_regex:/<[^>]*>/'],
            'address_line_2' => ['nullable', 'string', 'max:255', 'not_regex:/<[^>]*>/'],
            'city' => ['required', 'string', 'max:255', 'not_regex:/<[^>]*>/'],
            'county' => ['nullable', 'string', 'max:255', 'not_regex:/<[^>]*>/'],
            'postcode' => ['required', 'string', 'max:20', 'not_regex:/<[^>]*>/'],
            'country' => ['nullable', 'string', 'size:2', Rule::in(array_keys($this->countries()))],
        ])->after(function ($validator) use ($args, $customerId): void {
            $addressId = $args['input']['pickup_address_id'] ?? null;

            if (! $addressId) {
                return;
            }

            $ownsAddress = PickupAddress::query()
                ->where('pickup_address_id', $addressId)
                ->where('customer_id', $customerId)
                ->exists();

            if (! $ownsAddress) {
                $validator->errors()->add('pickup_address_id', 'The selected pickup address does not belong to the current customer.');
            }
        })->validate();

        $addressData = [
            'customer_id' => $customerId,
            'name' => $input['name'] ?? null,
            'phone' => $input['phone'] ?? null,
            'address_line_1' => $input['address_line_1'],
            'address_line_2' => $input['address_line_2'] ?? null,
            'city' => $input['city'],
            'county' => $input['county'] ?? null,
            'postcode' => $input['postcode'],
            'country' => $input['country'] ?: 'GB',
        ];

        if ($input['pickup_address_id'] ?? null) {
            $address = PickupAddress::query()
                ->where('pickup_address_id', $input['pickup_address_id'])
                ->where('customer_id', $customerId)
                ->firstOrFail();

            $address->update($addressData);
        } else {
            $address = PickupAddress::create($addressData);
        }

        return [
            'success' => true,
            'message' => ($input['pickup_address_id'] ?? null) ? 'Pickup address updated.' : 'Pickup address added.',
            'address' => $address,
        ];
    }

    private function countries(): array
    {
        return [
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'NZ' => 'New Zealand',
            'IE' => 'Ireland',
            'FR' => 'France',
            'DE' => 'Germany',
            'ES' => 'Spain',
            'IT' => 'Italy',
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'CH' => 'Switzerland',
            'AT' => 'Austria',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'DK' => 'Denmark',
            'FI' => 'Finland',
            'PT' => 'Portugal',
            'PL' => 'Poland',
        ];
    }
}
