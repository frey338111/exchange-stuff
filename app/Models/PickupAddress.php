<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'customer_id',
    'name',
    'phone',
    'address_line_1',
    'address_line_2',
    'city',
    'county',
    'postcode',
    'country',
])]
class PickupAddress extends Model
{
    protected $table = 'pickup_address';

    protected $primaryKey = 'pickup_address_id';

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}
