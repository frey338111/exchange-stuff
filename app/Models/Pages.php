<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'slug', 'content', 'published'])]
class Pages extends Model
{
    protected $table = 'pages';

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
        ];
    }
}
