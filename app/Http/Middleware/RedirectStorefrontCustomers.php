<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectStorefrontCustomers
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('customer_id')) {
            return redirect('/');
        }

        return $next($request);
    }
}
