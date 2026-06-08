<?php

namespace App\GraphQL\Queries;

use App\Models\ClaimRequest;
use Illuminate\Validation\ValidationException;

class GetMySentClaimRequestDetail
{
    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root, array $args): ClaimRequest
    {
        $request = request();

        if (! $request->hasSession() || ! $request->session()->has('customer_id')) {
            throw ValidationException::withMessages([
                'customer' => 'You must be logged in to view your sent request.',
            ]);
        }

        $claimRequest = ClaimRequest::query()
            ->with([
                'product.category',
                'product.productCondition',
                'messages.customer',
            ])
            ->where('customer_id', $request->session()->get('customer_id'))
            ->where('request_id', $args['request_id'])
            ->first();

        if (! $claimRequest) {
            throw ValidationException::withMessages([
                'request_id' => 'Sent request could not be found.',
            ]);
        }

        return $claimRequest;
    }
}
