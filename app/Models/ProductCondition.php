<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['condition_title', 'description', 'value_adjustment'])]
class ProductCondition extends Model
{
    protected $table = 'product_condition';

    protected $primaryKey = 'condition_id';

    protected function casts(): array
    {
        return [
            'value_adjustment' => 'float',
        ];
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'condition', 'condition_id');
    }
}
