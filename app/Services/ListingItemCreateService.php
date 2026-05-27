<?php

namespace App\Services;

use App\DTO\ListingItemCreateData;
use App\Models\ListingItem;

class ListingItemCreateService
{
    public function create(ListingItemCreateData $data): ListingItem
    {
        return ListingItem::create([
            'listing_id' => $data->listingId,
            'product_id' => $data->productId,
        ]);
    }
}
