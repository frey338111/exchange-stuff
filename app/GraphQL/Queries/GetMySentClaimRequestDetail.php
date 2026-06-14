<?php

namespace App\GraphQL\Queries;

use App\Models\ClaimRequest;
use App\Services\ValidationService;
use Illuminate\Validation\ValidationException;

class GetMySentClaimRequestDetail
{
    public function __construct(private readonly ValidationService $validationService) {}

    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root, array $args): ClaimRequest
    {
        $request = request();
        $customerId = $this->validationService->requireCustomerId(
            $request,
            'You must be logged in to view your sent request.',
        );

        $claimRequest = ClaimRequest::query()
            ->with([
                'product.category',
                'product.productCondition',
                'messages.customer',
            ])
            ->where('customer_id', $customerId)
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
