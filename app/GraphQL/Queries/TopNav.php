<?php

namespace App\GraphQL\Queries;

use App\Services\TopNavService;

class TopNav
{
    public function __construct(private readonly TopNavService $topNavService)
    {
    }

    public function __invoke(): array
    {
        return $this->topNavService->topNav();
    }
}
