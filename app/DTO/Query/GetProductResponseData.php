<?php

namespace App\DTO\Query;

use App\Models\Listing;
use App\Models\Product;

class GetProductResponseData
{
    public function __construct(
        public readonly Product $product,
        public readonly ?Listing $listing,
        public readonly bool $hasClaimRequest,
    ) {
    }
}
