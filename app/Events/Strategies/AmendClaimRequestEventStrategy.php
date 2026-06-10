<?php

namespace App\Events\Strategies;

use App\Events\ClaimRequestAmended;
use App\Models\ClaimRequest;

class AmendClaimRequestEventStrategy implements ClaimRequestEventStrategy
{
    public function dispatch(ClaimRequest $claimRequest, string $message, array $rejectedRequestIds): void
    {
        event(new ClaimRequestAmended(
            requestId: $claimRequest->request_id,
            message: $message !== '' ? $message : 'Your request has been amended.',
        ));
    }
}
