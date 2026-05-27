<?php

namespace App\Services;

use App\DTO\ListingCreateData;
use App\Models\Listing;

class ListingCreateService
{
    public function create(ListingCreateData $data): Listing
    {
        return Listing::create([
            'customer_id' => $data->customerId,
            'status' => Listing::STATUS_PENDING,
            'notes' => $data->notes,
        ]);
    }
}
