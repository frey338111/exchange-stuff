<?php

namespace App\Events\Strategies;

use App\Events\ClaimRequestAccepted;
use App\Events\ClaimRequestRejected;
use App\Models\ClaimRequest;

class AcceptClaimRequestEventStrategy implements ClaimRequestEventStrategy
{
    public function dispatch(ClaimRequest $claimRequest, string $message, array $rejectedRequestIds): void
    {
        event(new ClaimRequestAccepted(
            requestId: $claimRequest->request_id,
            message: $message !== '' ? $message : 'Your request has been accepted. The item is reserved for you.',
        ));

        foreach ($rejectedRequestIds as $rejectedRequestId) {
            event(new ClaimRequestRejected(
                requestId: $rejectedRequestId,
                message: 'the item you requested is not available anymore',
            ));
        }
    }
}
