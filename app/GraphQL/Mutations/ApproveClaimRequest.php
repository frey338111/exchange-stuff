<?php

namespace App\GraphQL\Mutations;

use App\Events\ClaimRequestAccepted;
use App\Events\ClaimRequestRejected;
use App\Models\ClaimRequest;
use App\Models\Listing;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApproveClaimRequest
{
    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root, array $args): array
    {
        $request = request();

        if (! $request->hasSession() || ! $request->session()->has('customer_id')) {
            throw ValidationException::withMessages([
                'customer' => 'You must be logged in to approve claim requests.',
            ]);
        }

        $customerId = (int) $request->session()->get('customer_id');

        $claimRequest = ClaimRequest::query()
            ->with('listing')
            ->where('request_id', $args['request_id'])
            ->first();

        if (! $claimRequest || ! $claimRequest->listing || (int) $claimRequest->listing->customer_id !== $customerId) {
            throw ValidationException::withMessages([
                'request_id' => 'Claim request could not be found.',
            ]);
        }

        if ((int) $claimRequest->status !== ClaimRequest::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'request_id' => 'Only pending claim requests can be approved.',
            ]);
        }

        $hasResolvedRequests = ClaimRequest::query()
            ->where('listing_id', $claimRequest->listing_id)
            ->where('status', '!=', ClaimRequest::STATUS_PENDING)
            ->exists();

        if ($hasResolvedRequests) {
            throw ValidationException::withMessages([
                'request_id' => 'This listing already has a resolved claim request.',
            ]);
        }

        $rejectedRequestIds = DB::transaction(function () use ($claimRequest): array {
            $claimRequest->update([
                'status' => ClaimRequest::STATUS_ACCEPTED,
            ]);

            $claimRequest->listing->update([
                'status' => Listing::STATUS_ACCEPTED,
            ]);

            $rejectedRequestIds = ClaimRequest::query()
                ->where('listing_id', $claimRequest->listing_id)
                ->where('request_id', '!=', $claimRequest->request_id)
                ->where('status', ClaimRequest::STATUS_PENDING)
                ->pluck('request_id')
                ->map(fn ($requestId) => (int) $requestId)
                ->all();

            ClaimRequest::query()
                ->whereIn('request_id', $rejectedRequestIds)
                ->update([
                    'status' => ClaimRequest::STATUS_REJECTED,
                ]);

            return $rejectedRequestIds;
        });

        event(new ClaimRequestAccepted(
            requestId: $claimRequest->request_id,
            message: 'Your request has been accepted. The item is reserved for you.',
        ));

        foreach ($rejectedRequestIds as $rejectedRequestId) {
            event(new ClaimRequestRejected(
                requestId: $rejectedRequestId,
                message: 'the item you requested is not available anymore',
            ));
        }

        return [
            'success' => true,
            'message' => 'Claim request approved.',
            'claim_request' => $claimRequest->fresh(['listing', 'product', 'customer']),
        ];
    }
}
