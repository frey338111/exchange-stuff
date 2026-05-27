<?php

namespace App\GraphQL\Queries\Response;

use App\DTO\Query\GetProductResponseData;
use Illuminate\Support\Facades\Storage;

class GetProductResponseBuilder
{
    public function build(GetProductResponseData $data): array
    {
        $product = $data->product;
        $listing = $data->listing;

        return [
            'listing_id' => $listing?->listing_id,
            'listing_customer_id' => $listing?->customer_id,
            'product_id' => $product->product_id,
            'product_name' => $product->product_name,
            'url_key' => $product->url_key,
            'sku' => $product->sku,
            'description' => $product->description,
            'category_title' => $product->category?->category_title,
            'condition_title' => $product->productCondition?->condition_title,
            'date_added' => $listing?->created_at?->toDateTimeString(),
            'listing_notes' => $listing?->notes,
            'has_claim_request' => $data->hasClaimRequest,
            'gallery_images' => $product->productGalleries
                ->sortBy(fn ($image) => match ($image->image_type) {
                    'main' => 0,
                    'other' => 1,
                    'thumbnail' => 2,
                    default => 3,
                })
                ->values()
                ->map(fn ($image) => [
                    'gallery_id' => $image->gallery_id,
                    'image_url' => Storage::disk('public')->url($image->image_path),
                    'image_type' => $image->image_type,
                ])
                ->all(),
        ];
    }
}
