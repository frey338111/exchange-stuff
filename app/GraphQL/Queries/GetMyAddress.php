<?php

namespace App\GraphQL\Queries;

use App\Models\PickupAddress;
use App\Services\ValidationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class GetMyAddress
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
            'You must be logged in to view your pickup addresses.',
        );

        return PickupAddress::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('created_at')
            ->get();
    }
}
