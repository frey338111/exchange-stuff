<?php

namespace App\GraphQL\Mutations;

use App\Services\CustomerAuthService;

class RegisterCustomer
{
    public function __construct(private readonly CustomerAuthService $customerAuthService)
    {
    }

    public function __invoke(mixed $root, array $args): array
    {
        return $this->customerAuthService->register($args['input'], request());
    }
}
