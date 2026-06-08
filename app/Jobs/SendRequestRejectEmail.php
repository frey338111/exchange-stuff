<?php

namespace App\Jobs;

use App\Mail\RequestRejectMail;
use App\Models\ClaimRequest;
use App\Models\ClaimRequestMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRequestRejectEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $requestId,
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

        $claimRequestMessage = ClaimRequestMessage::query()
            ->where('request_id', $claimRequest->request_id)
            ->latest('id')
            ->first();

        Mail::to($claimRequest->customer->email, $claimRequest->customer->name)
            ->send(new RequestRejectMail($claimRequest, $claimRequestMessage));
    }
}
