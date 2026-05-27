@php
    $productName = $claimRequest->product?->product_name ?? 'item';
    $claimer = $claimRequest->customer;
    $pickupDate = $claimRequest->pickup_date?->toDateString() ?? 'Decide by seller';
    $pickupSlot = $claimRequest->timeslot ?? 'Decide by seller';
    $title = "Claim request {$claimRequest->request_id}";
@endphp

@include('emails.partials.header', ['title' => $title])

<p style="margin: 0 0 8px; font-size: 14px; font-weight: 600; color: #4b5563;">Claim request</p>

<h1 style="margin: 0; font-size: 24px; line-height: 32px; font-weight: 700; color: #111827;">
    Your list item {{ $productName }} has a claim request {{ $claimRequest->request_id }}
</h1>

<p style="margin: 16px 0 0; font-size: 14px; line-height: 22px; color: #4b5563;">
    {{ $eventMessage }}
</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top: 24px; border-collapse: collapse; font-size: 14px;">
    <tr>
        <th align="left" style="width: 160px; border-top: 1px solid #e5e7eb; padding: 12px 12px 12px 0; color: #4b5563; font-weight: 600;">Request ID</th>
        <td style="border-top: 1px solid #e5e7eb; padding: 12px 0; color: #111827;">{{ $claimRequest->request_id }}</td>
    </tr>
    <tr>
        <th align="left" style="width: 160px; border-top: 1px solid #e5e7eb; padding: 12px 12px 12px 0; color: #4b5563; font-weight: 600;">Product</th>
        <td style="border-top: 1px solid #e5e7eb; padding: 12px 0; color: #111827;">{{ $productName }}</td>
    </tr>
    <tr>
        <th align="left" style="width: 160px; border-top: 1px solid #e5e7eb; padding: 12px 12px 12px 0; color: #4b5563; font-weight: 600;">Claimed by</th>
        <td style="border-top: 1px solid #e5e7eb; padding: 12px 0; color: #111827;">{{ $claimer?->name ?? 'Customer' }} &lt;{{ $claimer?->email ?? 'unknown' }}&gt;</td>
    </tr>
    <tr>
        <th align="left" style="width: 160px; border-top: 1px solid #e5e7eb; padding: 12px 12px 12px 0; color: #4b5563; font-weight: 600;">Pickup date</th>
        <td style="border-top: 1px solid #e5e7eb; padding: 12px 0; color: #111827;">{{ $pickupDate }}</td>
    </tr>
    <tr>
        <th align="left" style="width: 160px; border-top: 1px solid #e5e7eb; padding: 12px 12px 12px 0; color: #4b5563; font-weight: 600;">Pickup time</th>
        <td style="border-top: 1px solid #e5e7eb; padding: 12px 0; color: #111827;">{{ $pickupSlot }}</td>
    </tr>
    <tr>
        <th align="left" style="width: 160px; border-top: 1px solid #e5e7eb; padding: 12px 12px 0 0; color: #4b5563; font-weight: 600;">Notes</th>
        <td style="border-top: 1px solid #e5e7eb; padding: 12px 0 0; color: #111827;">{{ $claimRequest->notes ?: '-' }}</td>
    </tr>
</table>

@include('emails.partials.footer')
