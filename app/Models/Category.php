<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_title', 'url_key', 'parent_id', 'base_point', 'meta_tag', 'description', 'category_image', 'status'])]
class Category extends Model
{
    protected $table = 'category';

    protected $primaryKey = 'category_id';

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'base_point' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id');
    }
}
