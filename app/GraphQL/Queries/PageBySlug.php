<?php

namespace App\GraphQL\Queries;

use App\Models\Pages;

class PageBySlug
{
    public function __invoke(mixed $root, array $args): ?Pages
    {
        return Pages::query()
            ->select(['id', 'title', 'slug', 'content'])
            ->where('slug', $args['slug'])
            ->where('published', true)
            ->first();
    }
}
