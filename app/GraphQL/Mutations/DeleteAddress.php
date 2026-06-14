<?php

namespace App\GraphQL\Mutations;

use App\Models\PickupAddress;
use App\Services\ValidationService;
use Illuminate\Validation\ValidationException;

class DeleteAddress
{
    public function __construct(private readonly ValidationService $validationService) {}

    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root, array $args): array
    {
        $request = request();
        $customerId = $this->validationService->requireCustomerId(
            $request,
            'You must be logged in to delete a pickup address.',
        );

        $address = PickupAddress::query()
            ->where('pickup_address_id', $args['pickup_address_id'])
            ->where('customer_id', $customerId)
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
