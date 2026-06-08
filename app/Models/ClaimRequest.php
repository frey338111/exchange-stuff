<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['customer_id', 'listing_id', 'product_id', 'notes', 'pickup_date', 'timeslot', 'status'])]
class ClaimRequest extends Model
{
    public const STATUS_PENDING = 0;
    public const STATUS_ACCEPTED = 1;
    public const STATUS_REJECTED = 2;
    public const STATUS_AMENDED = 3;

    protected $table = 'claim_request';

    protected $primaryKey = 'request_id';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'listing_id' => 'integer',
            'product_id' => 'integer',
            'pickup_date' => 'datetime',
            'status' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'listing_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ClaimRequestMessage::class, 'request_id', 'request_id')->orderBy('id');
    }
}
