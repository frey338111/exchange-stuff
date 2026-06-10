<?php

namespace App\GraphQL\Mutations;

use App\Events\Strategies\AcceptClaimRequestEventStrategy;
use App\Events\Strategies\AmendClaimRequestEventStrategy;
use App\Events\Strategies\ClaimRequestEventStrategy;
use App\Events\Strategies\ClaimRequestEventStrategyProxy;
use App\Events\Strategies\RejectClaimRequestEventStrategy;
use App\Models\ClaimRequest;
use App\Models\ClaimRequestMessage;
use App\Models\PickupAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProcessClaimRequest
{
    /**
     * @var array<string, ClaimRequestEventStrategy>
     */
    private array $strategies;

    public function __construct()
    {
        $this->strategies = [
            'accept' => new ClaimRequestEventStrategyProxy(AcceptClaimRequestEventStrategy::class),
            'reject' => new ClaimRequestEventStrategyProxy(RejectClaimRequestEventStrategy::class),
            'amend' => new ClaimRequestEventStrategyProxy(AmendClaimRequestEventStrategy::class),
        ];
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root, array $args): array
    {
        $request = request();

        if (! $request->hasSession() || ! $request->session()->has('customer_id')) {
            throw ValidationException::withMessages([
                'customer' => 'You must be logged in to respond to claim requests.',
            ]);
        }

        $customerId = (int) $request->session()->get('customer_id');
        $input = $this->validateInput($args['input'] ?? [], $customerId);

        $claimRequest = ClaimRequest::query()
            ->with('listing')
            ->where('request_id', $input['request_id'])
            ->first();

        if (! $claimRequest || ! $claimRequest->listing || (int) $claimRequest->listing->customer_id !== $customerId) {
            throw ValidationException::withMessages([
                'request_id' => 'Claim request could not be found.',
            ]);
        }

        if ((int) $claimRequest->status !== ClaimRequest::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'request_id' => 'Only pending claim requests can be processed.',
            ]);
        }

        $action = $input['action'];
        $message = $this->buildMessage($input, $customerId);
        $rejectedRequestIds = [];

        DB::transaction(function () use ($action, $claimRequest, $input, $message, $customerId, &$rejectedRequestIds): void {
            $updates = [
                'status' => match ($action) {
                    'accept' => ClaimRequest::STATUS_ACCEPTED,
                    'reject' => ClaimRequest::STATUS_REJECTED,
                    'amend' => ClaimRequest::STATUS_AMENDED,
                },
            ];

            if (! empty($input['pickup_date'])) {
                $updates['pickup_date'] = $input['pickup_date'];
                $updates['timeslot'] = $input['pickup_slot'] ?? null;
            }

            $claimRequest->update($updates);

            if ($message !== '') {
                ClaimRequestMessage::create([
                    'request_id' => $claimRequest->request_id,
                    'customer_id' => $customerId,
                    'message' => $message,
                ]);
            }

            if ($action === 'amend') {
                return;
            }

            if ($action !== 'accept') {
                return;
            }

            $rejectedRequestIds = ClaimRequest::query()
                ->where('listing_id', $claimRequest->listing_id)
                ->where('request_id', '!=', $claimRequest->request_id)
                ->whereIn('status', [ClaimRequest::STATUS_PENDING, ClaimRequest::STATUS_AMENDED])
                ->pluck('request_id')
                ->map(fn ($requestId) => (int) $requestId)
                ->all();

            ClaimRequest::query()
                ->whereIn('request_id', $rejectedRequestIds)
                ->update([
                    'status' => ClaimRequest::STATUS_REJECTED,
                ]);

            $claimRequest->listing->update([
                'status' => \App\Models\Listing::STATUS_COMPLETED,
            ]);
        });

        $this->dispatchEvents($action, $claimRequest, $message, $rejectedRequestIds);

        return [
            'success' => true,
            'message' => match ($action) {
                'accept' => 'Claim request accepted.',
                'reject' => 'Claim request rejected.',
                'amend' => 'Claim request amended.',
            },
            'claim_request' => $claimRequest->fresh(['listing', 'product', 'customer']),
        ];
    }

    /**
     * @throws ValidationException
     */
    private function validateInput(array $input, int $customerId): array
    {
        return Validator::make($input, [
            'request_id' => ['required', 'integer', 'exists:claim_request,request_id'],
            'action' => ['required', 'string', Rule::in(['accept', 'reject', 'amend'])],
            'message' => ['nullable', 'string', 'max:5000', 'not_regex:/<[^>]*>/'],
            'stored_address_id' => [
                'nullable',
                'integer',
                Rule::exists('pickup_address', 'pickup_address_id')->where('customer_id', $customerId),
            ],
            'pickup_date' => ['nullable', 'date'],
            'pickup_slot' => [
                'nullable',
                'string',
                Rule::in(['7am-9am', '9am-12pm', '12pm-3pm', '3pm-6pm', '6pm-9pm']),
            ],
        ])->after(function ($validator) use ($input): void {
            if (($input['pickup_slot'] ?? null) && empty($input['pickup_date'])) {
                $validator->errors()->add('pickup_date', 'A pickup date is required when proposing another pickup time.');
            }
        })->validate();
    }

    private function buildMessage(array $input, int $customerId): string
    {
        $messageParts = [];
        $message = trim((string) ($input['message'] ?? ''));

        if ($message !== '') {
            $messageParts[] = $message;
        }

        if (! empty($input['stored_address_id'])) {
            $address = PickupAddress::query()
                ->where('customer_id', $customerId)
                ->where('pickup_address_id', $input['stored_address_id'])
                ->first();

            if ($address) {
                $messageParts[] = 'Collection address is: '.$this->formatAddress($address);
            }
        }

        return implode("\n\n", $messageParts);
    }

    private function formatAddress(PickupAddress $address): string
    {
        return collect([
            $address->address_line_1,
            $address->address_line_2,
            $address->city,
            $address->county,
            $address->postcode,
            $address->country,
        ])->filter()->implode(', ');
    }

    private function dispatchEvents(string $action, ClaimRequest $claimRequest, string $message, array $rejectedRequestIds): void
    {
        $this->strategies[$action]->dispatch($claimRequest, $message, $rejectedRequestIds);
    }
}
