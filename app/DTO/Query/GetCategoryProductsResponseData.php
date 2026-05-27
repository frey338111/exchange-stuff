<?php

namespace App\DTO\Query;

use App\Models\Category;
use Illuminate\Support\Collection;

class GetCategoryProductsResponseData
{
    public function __construct(
        public readonly ?Category $category,
        public readonly Collection $childCategories,
        public readonly Collection $products,
        public readonly Collection $pageProducts,
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $total,
    ) {
    }
}
