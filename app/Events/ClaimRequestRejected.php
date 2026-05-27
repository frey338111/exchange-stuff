<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClaimRequestRejected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $requestId,
        public readonly string $message,
    ) {
    }
}
