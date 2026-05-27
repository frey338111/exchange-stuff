<?php

namespace App\GraphQL\Queries;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class GetMyListing
{
    /**
     * @throws ValidationException
     */
    public function __invoke(mixed $root = null, array $args = []): Collection
    {
        $request = request();

        if (! $request->hasSession() || ! $request->session()->has('customer_id')) {
            throw ValidationException::withMessages([
                'customer' => 'You must be logged in to view your listings.',
            ]);
        }

        return Listing::query()
            ->with('products')
            ->where('customer_id', $request->session()->get('customer_id'))
            ->when(isset($args['status']), fn ($query) => $query->where('status', (int) $args['status']))
            ->orderByDesc('created_at')
            ->get();
    }
}
