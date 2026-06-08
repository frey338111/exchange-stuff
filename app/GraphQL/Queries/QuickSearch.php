<?php

namespace App\GraphQL\Queries;

use App\Models\Listing;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class QuickSearch
{
    public function __invoke(mixed $root, array $args): array
    {
        $searchItem = trim((string) ($args['searchItem'] ?? ''));

        if (mb_strlen($searchItem) <= 3) {
            return [];
        }

        return Product::query()
            ->select(['product_id', 'product_name', 'url_key'])
            ->with(['productGalleries' => fn ($query) => $query->where('image_type', 'thumbnail')])
            ->where('status', true)
            ->whereFullText('product_name', $searchItem)
            ->whereHas('listings', fn ($query) => $query->whereIn('listing.status', [Listing::STATUS_LIVE, Listing::STATUS_REQUESTED]))
            ->orderBy('product_name')
            ->limit(8)
            ->get()
            ->map(fn (Product $product) => [
                'product_name' => $product->product_name,
                'url_key' => $product->url_key,
                'thumbnail_url' => $this->thumbnailUrl($product),
            ])
            ->all();
    }

    private function thumbnailUrl(Product $product): ?string
    {
        $thumbnail = $product->productGalleries->first();

        return $thumbnail?->image_path ? Storage::disk('public')->url($thumbnail->image_path) : null;
    }
}
