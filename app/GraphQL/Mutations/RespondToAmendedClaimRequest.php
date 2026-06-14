<?php

namespace App\GraphQL\Mutations;

use App\Events\ClaimRequestAmended;
use App\Models\ClaimRequest;
use App\Models\ClaimRequestMessage;
use App\Services\ValidationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RespondToAmendedClaimRequest
{
    public function __construct(private readonly ValidationService $validationService) {}

    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root, array $args): array
    {
        $request = request();
        $customerId = $this->validationService->requireCustomerId(
            $request,
            'You must be logged in to reply to this request.',
        );
        $input = $this->validateInput($args['input'] ?? []);

        $claimRequest = ClaimRequest::query()
            ->where('customer_id', $customerId)
            ->where('request_id', $input['request_id'])
            ->first();

        if (! $claimRequest) {
            throw ValidationException::withMessages([
                'request_id' => 'Sent request could not be found.',
            ]);
        }

        if ((int) $claimRequest->status !== ClaimRequest::STATUS_AMENDED) {
            throw ValidationException::withMessages([
                'request_id' => 'Only amended requests can be replied to.',
            ]);
        }

        $message = trim((string) $input['message']);

        DB::transaction(function () use ($claimRequest, $customerId, $input, $message): void {
            $updates = [
                'status' => ClaimRequest::STATUS_PENDING,
            ];

            if (! empty($input['pickup_date'])) {
                $updates['pickup_date'] = $input['pickup_date'];
                $updates['timeslot'] = $input['pickup_slot'] ?? null;
            }

            $claimRequest->update($updates);

            ClaimRequestMessage::create([
                'request_id' => $claimRequest->request_id,
                'customer_id' => $customerId,
                'message' => $message,
            ]);
        });

        event(new ClaimRequestAmended(
            requestId: $claimRequest->request_id,
            message: $message,
        ));

        return [
            'success' => true,
            'message' => 'Reply sent.',
            'claim_request' => $claimRequest->fresh(['listing', 'product', 'customer', 'messages.customer']),
        ];
    }

    /**
     * @throws ValidationException
     */
    private function validateInput(array $input): array
    {
        return Validator::make($input, [
            'request_id' => ['required', 'integer', 'exists:claim_request,request_id'],
            'message' => ['required', 'string', 'max:5000', 'not_regex:/<[^>]*>/'],
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
}
