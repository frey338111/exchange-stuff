<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['product_name', 'url_key', 'sku', 'description', 'category_id', 'status', 'condition'])]
class Product extends Model
{
    protected $table = 'product';

    protected $primaryKey = 'product_id';

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'condition' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function productCondition(): BelongsTo
    {
        return $this->belongsTo(ProductCondition::class, 'condition', 'condition_id');
    }

    public function listings(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'listing_items', 'product_id', 'listing_id', 'product_id', 'listing_id');
    }

    public function productGalleries(): HasMany
    {
        return $this->hasMany(ProductGallery::class, 'product_id', 'product_id');
    }

    public function claimRequests(): HasMany
    {
        return $this->hasMany(ClaimRequest::class, 'product_id', 'product_id');
    }
}
