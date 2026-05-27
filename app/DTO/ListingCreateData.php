<?php

namespace App\DTO;

readonly class ListingCreateData
{
    public function __construct(
        public int $customerId,
        public ?string $notes,
    ) {
    }
}
