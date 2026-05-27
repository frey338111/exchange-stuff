<?php

namespace App\GraphQL\Mutations;

use App\Events\ClaimRequestCreated;
use App\Models\ClaimRequest;
use App\Models\Listing;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClaimItem
{
    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root, array $args): array
    {
        $request = request();

        if (! $request->hasSession() || ! $request->session()->has('customer_id')) {
            throw ValidationException::withMessages([
                'customer' => 'You must be logged in to claim this item.',
            ]);
        }

        $sessionCustomerId = (int) $request->session()->get('customer_id');

        $input = Validator::make($args['input'], [
            'customer_id' => ['required', 'integer', 'exists:customers,customer_id', Rule::in([$sessionCustomerId])],
            'listing_id' => ['required', 'integer', 'exists:listing,listing_id'],
            'product_id' => ['required', 'integer', 'exists:product,product_id'],
            'notes' => ['nullable', 'string', 'max:5000', 'not_regex:/<[^>]*>/'],
            'pickup_date' => ['nullable', 'date'],
            'pickup_slot' => [
                'nullable',
                'string',
                Rule::in(['7am-9am', '9am-12pm', '12pm-3pm', '3pm-6pm', '6pm-9pm']),
            ],
        ], [
            'customer_id.in' => 'The customer does not match the current session.',
        ])->after(function ($validator) use ($args, $sessionCustomerId): void {
            $input = $args['input'] ?? [];

            $listingHasProduct = Listing::query()
                ->where('listing_id', $input['listing_id'] ?? null)
                ->whereHas('products', fn ($query) => $query->where('product.product_id', $input['product_id'] ?? null))
                ->exists();

            if (! $listingHasProduct) {
                $validator->errors()->add('product_id', 'The selected product does not belong to this listing.');
            }

            $listingOwnerId = Listing::query()
                ->where('listing_id', $input['listing_id'] ?? null)
                ->value('customer_id');

            if ($listingOwnerId && (int) $listingOwnerId === $sessionCustomerId) {
                $validator->errors()->add('customer_id', 'You cannot claim your own item.');
            }

            if (($input['pickup_slot'] ?? null) && empty($input['pickup_date'])) {
                $validator->errors()->add('pickup_date', 'A pickup date is required when selecting a preferred time.');
            }
        })->validate();

        $claimRequest = ClaimRequest::query()
            ->where('customer_id', $sessionCustomerId)
            ->where('listing_id', $input['listing_id'])
            ->where('product_id', $input['product_id'])
            ->first();

        if ($claimRequest) {
            Listing::query()
                ->where('listing_id', $input['listing_id'])
                ->update(['status' => Listing::STATUS_REQUESTED]);

            return [
                'success' => true,
                'message' => 'Your claim request is waiting for seller response.',
                'claim_request' => $claimRequest,
            ];
        }

        $claimRequest = ClaimRequest::create([
            'customer_id' => $sessionCustomerId,
            'listing_id' => (int) $input['listing_id'],
            'product_id' => (int) $input['product_id'],
            'notes' => $input['notes'] ?? null,
            'pickup_date' => $input['pickup_date'] ?? null,
            'timeslot' => $input['pickup_slot'] ?? null,
            'status' => ClaimRequest::STATUS_PENDING,
        ]);

        Listing::query()
            ->where('listing_id', $input['listing_id'])
            ->update(['status' => Listing::STATUS_REQUESTED]);

        event(new ClaimRequestCreated(
            requestId: $claimRequest->request_id,
            message: "Claim request {$claimRequest->request_id} created.",
        ));

        return [
            'success' => true,
            'message' => 'Claim request sent.',
            'claim_request' => $claimRequest,
        ];
    }
}
