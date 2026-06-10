<?php

namespace App\Events\Strategies;

use App\Events\ClaimRequestRejected;
use App\Models\ClaimRequest;

class RejectClaimRequestEventStrategy implements ClaimRequestEventStrategy
{
    public function dispatch(ClaimRequest $claimRequest, string $message, array $rejectedRequestIds): void
    {
        event(new ClaimRequestRejected(
            requestId: $claimRequest->request_id,
            message: $message !== '' ? $message : 'Your request has been rejected.',
        ));
    }
}
