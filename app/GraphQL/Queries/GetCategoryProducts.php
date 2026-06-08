<?php

namespace App\GraphQL\Queries;

use App\GraphQL\Queries\Contracts\FilterInterface;
use App\DTO\Query\GetCategoryProductsResponseData;
use App\GraphQL\Queries\Response\GetCategoryProductsResposneBuilder;
use App\Models\Category;
use App\Models\Listing;
use App\Traits\DateTimeUtil;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class GetCategoryProducts
{
    use DateTimeUtil;

    public function __construct(
        private readonly GetCategoryProductsResposneBuilder $responseBuilder,
    ) {
    }

    public function __invoke(mixed $root, array $args): array
    {
        $page = max(1, (int) ($args['page'] ?? 1));
        $perPage = $this->perPage($args);
        $category = Category::query()
            ->where('url_key', $args['url_key'])
            ->where('status', true)
            ->first();

        if (! $category) {
            return $this->responseBuilder->build(new GetCategoryProductsResponseData(
                category: null,
                childCategories: collect(),
                products: collect(),
                pageProducts: collect(),
                page: $page,
                perPage: $perPage,
                total: 0,
            ));
        }

        $selectedCategoryId = isset($args['child_category_id']) ? (int) $args['child_category_id'] : null;
        $selectedConditionId = isset($args['condition_id']) ? (int) $args['condition_id'] : null;
        $selectedDateAdded = $args['date_added'] ?? null;
        $categoryIds = [(int) $category->category_id];
        $childCategories = collect();

        if ((int) $category->parent_id === 0) {
            $childCategories = Category::query()
                ->select(['category_id', 'category_title', 'url_key', 'description', 'category_image', 'parent_id'])
                ->where('parent_id', $category->category_id)
                ->wherein('status', [Listing::STATUS_LIVE,Listing::STATUS_REQUESTED])
                ->orderBy('category_title')
                ->get();

            $categoryIds = $childCategories
                ->pluck('category_id')
                ->map(fn ($categoryId) => (int) $categoryId)
                ->all();
        }

        $listings = Listing::query()
            ->with([
                'products.category',
                'products.productCondition',
                'products.productGalleries' => fn ($query) => $query->where('image_type', 'thumbnail'),
            ])
            ->whereIn('status', [Listing::STATUS_LIVE, Listing::STATUS_REQUESTED])
            ->whereHas('products', fn ($query) => $query->whereIn('product.category_id', $categoryIds))
            ->orderByDesc('created_at')
            ->get();

        $products = $this->productsFromListings($listings, $categoryIds);

        $categoryScopedProducts = $this->productsForCategory($products, $selectedCategoryId);
        $conditionScopedProducts = $this->productsForCondition($categoryScopedProducts, $selectedConditionId);
        $filteredProducts = $this->productsForDate($conditionScopedProducts, $selectedDateAdded);
        $total = $filteredProducts->count();
        $pageProducts = $filteredProducts
            ->forPage($page, $perPage)
            ->values();

        return $this->responseBuilder->build(new GetCategoryProductsResponseData(
            category: $category,
            childCategories: $childCategories,
            products: $products,
            pageProducts: $pageProducts,
            page: $page,
            perPage: $perPage,
            total: $total,
        ));
    }

    private function productsFromListings(Collection $listings, array $categoryIds): Collection
    {
        return $listings
            ->flatMap(fn (Listing $listing) => $listing->products
                ->filter(fn ($product) => in_array((int) $product->category_id, $categoryIds, true))
                ->map(fn ($product) => [
                    'listing_id' => $listing->listing_id,
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'url_key' => $product->url_key,
                    'category_id' => $product->category_id,
                    'thumbnail_url' => $this->thumbnailUrl($product),
                    'condition_title' => $product->productCondition?->condition_title,
                    'condition_id' => $product->condition,
                    'date_added' => $listing->created_at?->toDateTimeString(),
                    'date_bucket' => $this->dateBucket($listing->created_at),
                ]))
            ->values();
    }

    private function thumbnailUrl($product): ?string
    {
        $thumbnail = $product->productGalleries->first();

        return $thumbnail?->image_path ? Storage::disk('public')->url($thumbnail->image_path) : null;
    }

    private function productsForCategory(Collection $products, ?int $categoryId): Collection
    {
        if (! $categoryId) {
            return $products;
        }

        return $products
            ->filter(fn (array $product) => (int) $product['category_id'] === $categoryId)
            ->values();
    }

    private function productsForCondition(Collection $products, ?int $conditionId): Collection
    {
        if (! $conditionId) {
            return $products;
        }

        return $products
            ->filter(fn (array $product) => (int) $product['condition_id'] === $conditionId)
            ->values();
    }

    private function productsForDate(Collection $products, ?string $dateAdded): Collection
    {
        if (! $dateAdded || ! array_key_exists($dateAdded, FilterInterface::DATE_FILTERS)) {
            return $products;
        }

        return $products
            ->filter(fn (array $product) => $product['date_bucket'] === $dateAdded)
            ->values();
    }

    private function perPage(array $args): int
    {
        $perPage = (int) ($args['per_page'] ?? 12);

        return in_array($perPage, [12, 24, 36], true) ? $perPage : 12;
    }

}
