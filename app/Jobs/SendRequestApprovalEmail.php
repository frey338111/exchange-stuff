<?php

namespace App\Jobs;

use App\Mail\RequestApprovalMail;
use App\Models\ClaimRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRequestApprovalEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $requestId,
        private readonly string $eventMessage,
    ) {
    }

    public function handle(): void
    {
        $claimRequest = ClaimRequest::query()
            ->with(['customer', 'listing.customer', 'product'])
            ->find($this->requestId);

        if (! $claimRequest?->customer?->email) {
            return;
        }

        Mail::to($claimRequest->customer->email, $claimRequest->customer->name)
            ->send(new RequestApprovalMail($claimRequest, $this->eventMessage));
    }
}
