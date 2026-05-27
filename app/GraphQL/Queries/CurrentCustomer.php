<?php

namespace App\GraphQL\Queries;

use App\Services\CustomerAuthService;

class CurrentCustomer
{
    public function __construct(private readonly CustomerAuthService $customerAuthService)
    {
    }

    public function __invoke(): ?array
    {
        return $this->customerAuthService->currentCustomer(request());
    }
}
