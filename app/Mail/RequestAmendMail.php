<?php

namespace App\Mail;

use App\Models\ClaimRequest;
use App\Models\ClaimRequestMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestAmendMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly ClaimRequest $claimRequest,
        public readonly ?ClaimRequestMessage $claimRequestMessage,
    ) {
    }

    public function envelope(): Envelope
    {
        $productName = $this->claimRequest->product?->product_name ?? 'item';

        return new Envelope(
            subject: "Request for {$productName} has an update",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.request-amend',
            with: [
                'claimRequest' => $this->claimRequest,
                'claimRequestMessage' => $this->claimRequestMessage,
            ],
        );
    }
}
