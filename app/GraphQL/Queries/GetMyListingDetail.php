<?php

namespace App\GraphQL\Queries;

use App\Models\Listing;
use App\Services\ValidationService;
use Illuminate\Validation\ValidationException;

class GetMyListingDetail
{
    public function __construct(private readonly ValidationService $validationService) {}

    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root, array $args): Listing
    {
        $request = request();
        $customerId = $this->validationService->requireCustomerId(
            $request,
            'You must be logged in to view your listing.',
        );

        $listing = Listing::query()
            ->with([
                'products.category',
                'products.productCondition',
                'claimRequests.customer',
                'claimRequests.product',
                'claimRequests.messages.customer',
            ])
            ->where('customer_id', $customerId)
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
