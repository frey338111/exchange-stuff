<?php

namespace App\GraphQL\Queries;

use App\Models\PickupAddress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class GetMyAddress
{
    /**
     * @throws ValidationException
     */
    public function __invoke(): Collection
    {
        $request = request();

        if (! $request->hasSession() || ! $request->session()->has('customer_id')) {
            throw ValidationException::withMessages([
                'customer' => 'You must be logged in to view your pickup addresses.',
            ]);
        }

        return PickupAddress::query()
            ->where('customer_id', $request->session()->get('customer_id'))
            ->orderByDesc('created_at')
            ->get();
    }
}
