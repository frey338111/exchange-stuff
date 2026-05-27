<?php

namespace App\GraphQL\Mutations;

use App\Services\CustomerAuthService;

class LoginCustomer
{
    public function __construct(private readonly CustomerAuthService $customerAuthService)
    {
    }

    public function __invoke(mixed $root, array $args): array
    {
        return $this->customerAuthService->login($args['input'], request());
    }
}
