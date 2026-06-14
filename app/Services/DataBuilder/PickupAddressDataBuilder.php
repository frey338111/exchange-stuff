<?php

namespace App\Services\DataBuilder;

use App\DTO\PickupAddressData;

class PickupAddressDataBuilder
{
    public function build(PickupAddressData $data): array
    {
        return [
            'customer_id' => $data->customerId,
            'name' => $data->name,
            'phone' => $data->phone,
            'address_line_1' => $data->addressLine1,
            'address_line_2' => $data->addressLine2,
            'city' => $data->city,
            'county' => $data->county,
            'postcode' => $data->postcode,
            'country' => $data->country ?: 'GB',
        ];
    }
}
