<?php

namespace App\GraphQL\Queries;

use App\DTO\Query\GetProductResponseData;
use App\GraphQL\Queries\Response\GetProductResponseBuilder;
use App\Models\ClaimRequest;
use App\Models\Listing;
use App\Models\Product;

class GetProduct
{
    public function __construct(
        private readonly GetProductResponseBuilder $responseBuilder,
    ) {
    }

    public function __invoke(mixed $root, array $args): ?array
    {
        $product = Product::query()
            ->with([
                'category',
                'productCondition',
                'productGalleries' => fn ($query) => $query->where('image_type', '!=', 'thumbnail'),
                'listings' => fn ($query) => $query
                    ->wherein('listing.status', [Listing::STATUS_LIVE,Listing::STATUS_REQUESTED])
                    ->orderByDesc('listing.created_at'),
            ])
            ->where('url_key', $args['url_key'])
            ->where('status', true)
            ->whereHas('listings', fn ($query) => $query->where('listing.status', Listing::STATUS_LIVE))
            ->first();

        if (! $product) {
            return null;
        }

        $listing = $product->listings->first();
        $customerId = request()->hasSession() ? request()->session()->get('customer_id') : null;
        $hasClaimRequest = $customerId && $listing
            ? ClaimRequest::query()
                ->where('customer_id', $customerId)
                ->where('listing_id', $listing->listing_id)
                ->where('product_id', $product->product_id)
                ->exists()
            : false;

        return $this->responseBuilder->build(new GetProductResponseData(
            product: $product,
            listing: $listing,
            hasClaimRequest: $hasClaimRequest,
        ));
    }
}
