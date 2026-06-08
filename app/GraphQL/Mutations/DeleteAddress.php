<?php

namespace App\GraphQL\Mutations;

use App\Models\PickupAddress;
use Illuminate\Validation\ValidationException;

class DeleteAddress
{
    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root, array $args): array
    {
        $request = request();

        if (! $request->hasSession() || ! $request->session()->has('customer_id')) {
            throw ValidationException::withMessages([
                'customer' => 'You must be logged in to delete a pickup address.',
            ]);
        }

        $address = PickupAddress::query()
            ->where('pickup_address_id', $args['pickup_address_id'])
            ->where('customer_id', $request->session()->get('customer_id'))
            ->first();

        if (! $address) {
            throw ValidationException::withMessages([
                'pickup_address_id' => 'The selected pickup address could not be found.',
            ]);
        }

        $address->delete();

        return [
            'success' => true,
            'message' => 'Pickup address deleted.',
        ];
    }
}
