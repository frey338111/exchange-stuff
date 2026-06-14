<?php

namespace App\GraphQL\Mutations;

use App\DTO\PickupAddressData;
use App\Models\PickupAddress;
use App\Services\DataBuilder\PickupAddressDataBuilder;
use App\Services\ValidationService;
use App\Traits\DirectoryTraitUtil;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AddAddress
{
    use DirectoryTraitUtil;

    public function __construct(
        private readonly PickupAddressDataBuilder $addressDataBuilder,
        private readonly ValidationService $validationService,
    ) {}

    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root, array $args): array
    {
        $request = request();
        $customerId = $this->validationService->requireCustomerId(
            $request,
            'You must be logged in to save a pickup address.',
        );

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

        $addressData = $this->addressDataBuilder->build(new PickupAddressData(
            customerId: $customerId,
            name: $input['name'] ?? null,
            phone: $input['phone'] ?? null,
            addressLine1: $input['address_line_1'],
            addressLine2: $input['address_line_2'] ?? null,
            city: $input['city'],
            county: $input['county'] ?? null,
            postcode: $input['postcode'],
            country: $input['country'] ?? null,
        ));

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
}
