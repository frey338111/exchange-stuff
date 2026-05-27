<?php

namespace App\Mail;

use App\Models\ClaimRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestApprovalMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly ClaimRequest $claimRequest,
        public readonly string $eventMessage,
    ) {
    }

    public function envelope(): Envelope
    {
        $productName = $this->claimRequest->product?->product_name ?? 'item';

        return new Envelope(
            subject: "Your request for {$productName} has been accepted",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.request-approval',
        );
    }
}
