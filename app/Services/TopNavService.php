<?php

namespace App\Services;

use App\Models\Category;

class TopNavService
{
    public function topNav(): array
    {
        $categories = Category::query()
            ->select(['category_id', 'category_title', 'url_key', 'parent_id'])
            ->where('status', true)
            ->orderBy('parent_id')
            ->orderBy('category_title')
            ->get();

        $nodes = [];
        $topNav = [];

        foreach ($categories as $category) {
            $nodes[$category->category_id] = [
                'category_id' => $category->category_id,
                'title' => $category->category_title,
                'url_key' => $category->url_key,
                'children' => [],
            ];

            if ((int) $category->parent_id === 0) {
                $topNav[] = &$nodes[$category->category_id];

                continue;
            }

            if (isset($nodes[$category->parent_id])) {
                $nodes[$category->parent_id]['children'][] = &$nodes[$category->category_id];
            }
        }

        return $topNav;
    }
}
