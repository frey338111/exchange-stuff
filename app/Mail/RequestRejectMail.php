<?php

namespace App\Mail;

use App\Models\ClaimRequest;
use App\Models\ClaimRequestMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestRejectMail extends Mailable
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
            subject: "Your request for {$productName} was not accepted",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.request-reject',
            with: [
                'claimRequest' => $this->claimRequest,
                'claimRequestMessage' => $this->claimRequestMessage,
            ],
        );
    }
}
