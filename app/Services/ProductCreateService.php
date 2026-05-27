<?php

namespace App\Services;

use App\DTO\ProductCreateData;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductCreateService
{
    public function create(ProductCreateData $data): Product
    {
        return Product::create([
            'product_name' => $data->name,
            'url_key' => $this->uniqueProductUrlKey($data->name),
            'sku' => $this->makeSku(),
            'description' => $data->description,
            'category_id' => $data->categoryId,
            'status' => true,
            'condition' => $data->conditionId,
        ]);
    }

    private function makeSku(): string
    {
        return 'LIST-'.Str::upper(Str::random(12));
    }

    private function uniqueProductUrlKey(string $productName): string
    {
        $baseUrlKey = Str::slug($productName);
        $baseUrlKey = $baseUrlKey !== '' ? $baseUrlKey : 'product';
        $urlKey = $baseUrlKey;
        $suffix = 2;

        while (Product::query()->where('url_key', $urlKey)->exists()) {
            $urlKey = $baseUrlKey.'-'.$suffix;
            $suffix++;
        }

        return $urlKey;
    }
}
