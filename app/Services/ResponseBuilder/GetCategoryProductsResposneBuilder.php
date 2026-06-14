<?php

namespace App\Services\ResponseBuilder;

use App\DTO\Query\GetCategoryProductsResponseData;
use App\GraphQL\Queries\Contracts\FilterInterface;
use App\Models\Category;
use App\Services\CategoryImageService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class GetCategoryProductsResposneBuilder
{
    public function __construct(
        private readonly CategoryImageService $categoryImageService,
    ) {}

    public function build(GetCategoryProductsResponseData $data): array
    {
        if (! $data->category) {
            return [
                'category' => null,
                'products' => [],
                'filters' => [
                    'total' => 0,
                    'child_categories' => [],
                    'conditions' => [],
                    'date_added' => $this->emptyDateFilters(),
                ],
                'pagination' => $this->pagination($data->page, $data->perPage, 0),
            ];
        }

        return [
            'category' => $this->category($data->category, $data->childCategories),
            'products' => $this->products($data->pageProducts),
            'filters' => [
                'total' => $data->products->count(),
                'child_categories' => $this->childCategoryFilters($data->childCategories, $data->products),
                'conditions' => $this->conditionFilters($data->products),
                'date_added' => $this->dateFilters($data->products),
            ],
            'pagination' => $this->pagination($data->page, $data->perPage, $data->total),
        ];
    }

    private function category(Category $category, Collection $childCategories): array
    {
        return [
            'category_id' => $category->category_id,
            'category_title' => $category->category_title,
            'url_key' => $category->url_key,
            'description' => $category->description,
            'category_image' => $category->category_image ? Storage::disk('public')->url($category->category_image) : null,
            'category_image_mobile' => $this->categoryImageService->mobileUrl($category->category_image),
            'parent_id' => $category->parent_id,
            'children' => $childCategories
                ->map(fn (Category $childCategory) => [
                    'category_id' => $childCategory->category_id,
                    'category_title' => $childCategory->category_title,
                    'url_key' => $childCategory->url_key,
                    'description' => $childCategory->description,
                    'category_image' => $childCategory->category_image ? Storage::disk('public')->url($childCategory->category_image) : null,
                    'category_image_mobile' => $this->categoryImageService->mobileUrl($childCategory->category_image),
                    'parent_id' => $childCategory->parent_id,
                    'children' => [],
                ])
                ->all(),
        ];
    }

    private function products(Collection $products): array
    {
        return $products
            ->map(fn (array $product) => collect($product)->except('date_bucket')->all())
            ->values()
            ->all();
    }

    private function childCategoryFilters(Collection $childCategories, Collection $products): array
    {
        $counts = $products->countBy(fn (array $product) => (string) $product['category_id']);

        return $childCategories
            ->map(fn (Category $category) => [
                'id' => (string) $category->category_id,
                'title' => $category->category_title,
                'count' => $counts->get((string) $category->category_id, 0),
            ])
            ->values()
            ->all();
    }

    private function conditionFilters(Collection $products): array
    {
        return $products
            ->filter(fn (array $product) => filled($product['condition_id']))
            ->groupBy(fn (array $product) => (string) $product['condition_id'])
            ->map(fn (Collection $products, string $conditionId) => [
                'id' => $conditionId,
                'title' => $products->first()['condition_title'] ?? '-',
                'count' => $products->count(),
            ])
            ->sortBy('title')
            ->values()
            ->all();
    }

    private function dateFilters(Collection $products): array
    {
        $counts = $products->countBy('date_bucket');

        return collect(FilterInterface::DATE_FILTERS)
            ->map(fn (string $title, string $id) => [
                'id' => $id,
                'title' => $title,
                'count' => $counts->get($id, 0),
            ])
            ->values()
            ->all();
    }

    private function emptyDateFilters(): array
    {
        return collect(FilterInterface::DATE_FILTERS)
            ->map(fn (string $title, string $id) => [
                'id' => $id,
                'title' => $title,
                'count' => 0,
            ])
            ->values()
            ->all();
    }

    private function pagination(int $page, int $perPage, int $total): array
    {
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);

        return [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
            'to' => min($page * $perPage, $total),
        ];
    }
}
