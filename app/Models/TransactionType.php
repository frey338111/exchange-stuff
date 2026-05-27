<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title'])]
class TransactionType extends Model
{
    protected $table = 'transaction_type';

    protected $primaryKey = 'type_id';

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'type_id', 'type_id');
    }
}
