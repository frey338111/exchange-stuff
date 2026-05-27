<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'title' => 'Clothes',
                'description' => 'Children clothing, everyday outfits, seasonal wear, and wardrobe essentials.',
                'meta_tag' => 'children clothing, everyday outfits, seasonal wear, wardrobe essentials',
                'subs' => [
                    ['title' => 'baby clothes', 'base_point' => 40, 'description' => 'Soft baby clothes including bodysuits, sleepwear, and everyday infant outfits.'],
                    ['title' => 'shoes', 'base_point' => 60, 'description' => 'Children shoes for daily wear, school, play, and special occasions.'],
                    ['title' => 'coats', 'base_point' => 70, 'description' => 'Warm children coats, jackets, and outerwear for colder weather.'],
                ],
            ],
            [
                'title' => 'Toys',
                'description' => 'Popular children toys for creative play, learning, collecting, and family activities.',
                'meta_tag' => 'children toys, creative play, learning, collecting, family activities',
                'subs' => [
                    ['title' => 'LEGO', 'base_point' => 90, 'description' => 'LEGO sets, building bricks, and construction toys for imaginative play.'],
                    ['title' => 'dolls', 'base_point' => 50, 'description' => 'Dolls, figures, accessories, and pretend play toys for children.'],
                    ['title' => 'puzzles', 'base_point' => 30, 'description' => 'Children puzzles that support problem solving, focus, and early learning.'],
                    ['title' => 'games', 'base_point' => 40, 'description' => 'Board games, card games, and family games for children and groups.'],
                ],
            ],
            [
                'title' => 'Books',
                'description' => 'Children books for reading, learning, bedtime stories, and early development.',
                'meta_tag' => 'children books, reading, learning, bedtime stories, early development',
                'subs' => [
                    ['title' => 'story books', 'base_point' => 30, 'description' => 'Story books, picture books, and bedtime books for young readers.'],
                    ['title' => 'learning books', 'base_point' => 40, 'description' => 'Educational books for letters, numbers, language, and early skills.'],
                ],
            ],
            [
                'title' => 'Baby',
                'description' => 'Baby equipment and everyday care items for travel, feeding, and carrying.',
                'meta_tag' => 'baby equipment, care items, travel, feeding, carrying',
                'subs' => [
                    ['title' => 'stroller', 'base_point' => 100, 'description' => 'Baby strollers, pushchairs, and travel systems for daily transport.'],
                    ['title' => 'feeding items', 'base_point' => 50, 'description' => 'Baby feeding items including bottles, sterilizers, highchairs, and weaning accessories.'],
                    ['title' => 'carriers', 'base_point' => 80, 'description' => 'Baby carriers, wraps, and slings for hands-free carrying.'],
                ],
            ],
            [
                'title' => 'Outdoor',
                'description' => 'Outdoor toys and active equipment for play, exercise, sports, and riding.',
                'meta_tag' => 'outdoor toys, active equipment, play, exercise, sports, riding',
                'subs' => [
                    ['title' => 'bikes', 'base_point' => 90, 'description' => 'Children bikes, balance bikes, and cycling accessories for outdoor riding.'],
                    ['title' => 'scooters', 'base_point' => 70, 'description' => 'Children scooters, ride-on toys, and outdoor mobility toys.'],
                    ['title' => 'sports toys', 'base_point' => 50, 'description' => 'Sports toys, balls, nets, and active play equipment for children.'],
                ],
            ],
        ];

        foreach ($categories as $category) {
            $parent = Category::updateOrCreate(
                [
                    'category_title' => $category['title'],
                    'parent_id' => 0,
                ],
                [
	                    'base_point' => 0,
	                    'url_key' => Str::slug($category['title']),
	                    'meta_tag' => $category['meta_tag'],
                    'description' => $category['description'],
                    'status' => true,
                ],
            );

            foreach ($category['subs'] as $subCategory) {
                Category::updateOrCreate(
                    [
                        'category_title' => $subCategory['title'],
                        'parent_id' => $parent->category_id,
                    ],
                    [
	                        'base_point' => $subCategory['base_point'],
	                        'url_key' => Str::slug($subCategory['title']),
	                        'meta_tag' => $this->metaTagsFromDescription($subCategory['description']),
                        'description' => $subCategory['description'],
                        'status' => true,
                    ],
                );
            }
        }
    }

    private function metaTagsFromDescription(string $description): string
    {
        return strtolower(str_replace(['.', ' including ', ' and ', ' for '], ['', ', ', ', ', ', '], $description));
    }
}
