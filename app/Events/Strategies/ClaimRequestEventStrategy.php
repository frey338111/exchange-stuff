<?php

namespace App\Events\Strategies;

use App\Models\ClaimRequest;

interface ClaimRequestEventStrategy
{
    public function dispatch(ClaimRequest $claimRequest, string $message, array $rejectedRequestIds): void;
}
