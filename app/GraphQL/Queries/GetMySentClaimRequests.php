<?php

namespace App\GraphQL\Queries;

use App\Models\ClaimRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class GetMySentClaimRequests
{
    /**
     * @throws ValidationException
     */
    public function __invoke(): Collection
    {
        $request = request();

        if (! $request->hasSession() || ! $request->session()->has('customer_id')) {
            throw ValidationException::withMessages([
                'customer' => 'You must be logged in to view your sent requests.',
            ]);
        }

        return ClaimRequest::query()
            ->with('product')
            ->where('customer_id', $request->session()->get('customer_id'))
            ->orderByDesc('request_id')
            ->get();
    }
}
