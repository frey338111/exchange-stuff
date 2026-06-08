<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['customer_id', 'status', 'notes'])]
class Listing extends Model
{
    public const STATUS_PENDING = 0;
    public const STATUS_LIVE = 1;
    public const STATUS_REJECTED = 2;
    public const STATUS_COMPLETED = 3;

    protected $table = 'listing';

    protected $primaryKey = 'listing_id';

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ListingItem::class, 'listing_id', 'listing_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'listing_items', 'listing_id', 'product_id', 'listing_id', 'product_id');
    }

    public function claimRequests(): HasMany
    {
        return $this->hasMany(ClaimRequest::class, 'listing_id', 'listing_id');
    }
}
