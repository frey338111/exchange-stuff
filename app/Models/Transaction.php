<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['customer_id', 'amount', 'type_id', 'notes'])]
class Transaction extends Model
{
    protected $primaryKey = 'transaction_id';

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'type_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class, 'type_id', 'type_id');
    }
}
