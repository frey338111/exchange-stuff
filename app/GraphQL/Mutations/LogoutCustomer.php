<?php

namespace App\GraphQL\Mutations;

use App\Services\CustomerAuthService;

class LogoutCustomer
{
    public function __construct(private readonly CustomerAuthService $customerAuthService)
    {
    }

    public function __invoke(): bool
    {
        return $this->customerAuthService->logout(request());
    }
}
