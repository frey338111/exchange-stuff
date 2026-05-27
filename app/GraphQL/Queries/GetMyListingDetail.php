<?php

namespace App\GraphQL\Queries;

use App\Models\Listing;
use Illuminate\Validation\ValidationException;

class GetMyListingDetail
{
    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root, array $args): Listing
    {
        $request = request();

        if (! $request->hasSession() || ! $request->session()->has('customer_id')) {
            throw ValidationException::withMessages([
                'customer' => 'You must be logged in to view your listing.',
            ]);
        }

        $listing = Listing::query()
            ->with(['products.category', 'products.productCondition', 'claimRequests.customer', 'claimRequests.product'])
            ->where('customer_id', $request->session()->get('customer_id'))
            ->where('listing_id', $args['listing_id'])
            ->first();

        if (! $listing) {
            throw ValidationException::withMessages([
                'listing_id' => 'Listing could not be found.',
            ]);
        }

        return $listing;
    }
}
