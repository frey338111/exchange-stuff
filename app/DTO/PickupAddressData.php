<?php

namespace App\DTO;

readonly class PickupAddressData
{
    public function __construct(
        public int $customerId,
        public ?string $name,
        public ?string $phone,
        public string $addressLine1,
        public ?string $addressLine2,
        public string $city,
        public ?string $county,
        public string $postcode,
        public ?string $country,
    ) {}
}
