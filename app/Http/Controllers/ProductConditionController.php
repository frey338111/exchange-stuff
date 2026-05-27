<?php

namespace App\Http\Controllers;

use App\Models\ProductCondition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductConditionController extends Controller
{
    public function index(): View
    {
        $productConditions = ProductCondition::query()
            ->orderBy('condition_title')
            ->get();

        return view('product-conditions.index', [
            'productConditions' => $productConditions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ProductCondition::create($this->validatedProductConditionData($request));

        return redirect()->route('product-conditions.index')->with('status', 'Product condition created.');
    }

    public function update(Request $request, ProductCondition $productCondition): RedirectResponse
    {
        $productCondition->update($this->validatedProductConditionData($request));

        return redirect()->route('product-conditions.index')->with('status', 'Product condition updated.');
    }

    private function validatedProductConditionData(Request $request): array
    {
        return $request->validate([
            'condition_title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'value_adjustment' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);
    }
}
