<?php

namespace App\GraphQL\Queries;

use App\Models\ProductCondition;
use Illuminate\Database\Eloquent\Collection;

class ProductConditions
{
    public function __invoke(): Collection
    {
        return ProductCondition::query()
            ->orderBy('condition_id')
            ->get();
    }
}
