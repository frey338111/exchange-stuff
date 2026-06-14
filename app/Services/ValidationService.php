<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ValidationService
{
    /**
     * @throws ValidationException
     */
    public function requireCustomerId(Request $request, string $message): int
    {
        if (! $request->hasSession() || ! $request->session()->has('customer_id')) {
            throw ValidationException::withMessages([
                'customer' => $message,
            ]);
        }

        return (int) $request->session()->get('customer_id');
    }
}
