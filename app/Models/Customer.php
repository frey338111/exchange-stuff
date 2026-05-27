<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'password', 'phone', 'address', 'balance', 'status'])]
class Customer extends Model
{
    protected $primaryKey = 'customer_id';

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'customer_id', 'customer_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'customer_id', 'customer_id');
    }

    public function claimRequests(): HasMany
    {
        return $this->hasMany(ClaimRequest::class, 'customer_id', 'customer_id');
    }
}
