<?php

namespace Database\Seeders;

use App\Models\ProductCondition;
use Illuminate\Database\Seeder;

class ProductConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conditions = [
            [
                'condition_title' => 'New',
                'description' => 'Unused product in original or complete packaging with no signs of wear.',
                'value_adjustment' => 1.0,
            ],
            [
                'condition_title' => 'Mint',
                'description' => 'Like new product with no visible marks, damage, or meaningful wear.',
                'value_adjustment' => 0.9,
            ],
            [
                'condition_title' => 'Excellent',
                'description' => 'Very lightly used product with minor signs of handling but fully working.',
                'value_adjustment' => 0.75,
            ],
            [
                'condition_title' => 'Good',
                'description' => 'Used product with normal wear, small marks, and good working condition.',
                'value_adjustment' => 0.6,
            ],
            [
                'condition_title' => 'Fair',
                'description' => 'Well used product with noticeable wear, cosmetic marks, but still usable.',
                'value_adjustment' => 0.4,
            ],
        ];

        foreach ($conditions as $condition) {
            ProductCondition::updateOrCreate(
                ['condition_title' => $condition['condition_title']],
                [
                    'description' => $condition['description'],
                    'value_adjustment' => $condition['value_adjustment'],
                ],
            );
        }
    }
}
