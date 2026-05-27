<?php

namespace App\DTO;

readonly class ListingItemCreateData
{
    public function __construct(
        public int $listingId,
        public int $productId,
    ) {
    }
}
