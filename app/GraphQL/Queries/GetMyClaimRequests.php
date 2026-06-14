<?php

namespace App\GraphQL\Queries;

use App\Models\ClaimRequest;
use App\Services\ValidationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class GetMyClaimRequests
{
    public function __construct(private readonly ValidationService $validationService) {}

    /**
     * @throws ValidationException
     */
    public function __invoke(): Collection
    {
        $request = request();
        $customerId = $this->validationService->requireCustomerId(
            $request,
            'You must be logged in to view your claim requests.',
        );

        return ClaimRequest::query()
            ->with(['listing.products', 'product'])
            ->whereHas('listing', fn ($query) => $query->where('customer_id', $customerId))
            ->orderByDesc('request_id')
            ->get();
    }
}
