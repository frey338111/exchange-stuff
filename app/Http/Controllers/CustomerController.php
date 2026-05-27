<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';
        $sortColumns = [
            'customer_id',
            'name',
            'email',
            'phone',
            'balance',
            'status',
            'created_at',
        ];

        if (! in_array($sort, $sortColumns, true)) {
            $sort = 'created_at';
        }

        $customers = Customer::query()
            ->when($search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        return view('customers.index', [
            'customers' => $customers,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }
}
