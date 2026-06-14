<?php

namespace App\GraphQL\Queries;

use App\Models\Listing;
use App\Services\ValidationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class GetMyListing
{
    public function __construct(private readonly ValidationService $validationService) {}

    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root = null, array $args = []): Collection
    {
        $request = request();
        $customerId = $this->validationService->requireCustomerId(
            $request,
            'You must be logged in to view your listings.',
        );

        return Listing::query()
            ->with('products')
            ->where('customer_id', $customerId)
            ->when(isset($args['status']), fn ($query) => $query->where('status', (int) $args['status']))
            ->orderByDesc('created_at')
            ->get();
    }
}
