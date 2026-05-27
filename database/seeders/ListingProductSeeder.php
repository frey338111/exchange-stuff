<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Listing;
use App\Models\ListingItem;
use App\Models\Product;
use App\Models\ProductCondition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ListingProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = collect(range(1, 10))->map(function (int $index): Customer {
            return Customer::updateOrCreate(
                ['email' => "customer{$index}@example.com"],
                [
                    'name' => "Customer {$index}",
                    'password' => Hash::make('password'),
                    'phone' => '07000'.str_pad((string) $index, 6, '0', STR_PAD_LEFT),
                    'address' => "{$index} Example Street",
                    'balance' => fake()->numberBetween(0, 500),
                    'status' => true,
                ],
            );
        });

        $categories = Category::query()
            ->where('status', true)
            ->where('parent_id', '!=', 0)
            ->get();

        if ($categories->isEmpty()) {
            $categories = Category::query()
                ->where('status', true)
                ->get();
        }

        $conditions = ProductCondition::query()->get();

        if ($categories->isEmpty() || $conditions->isEmpty()) {
            return;
        }

        foreach (range(1, 200) as $index) {
            $customer = $customers->random();
            $category = $categories->random();
            $condition = $conditions->random();

            $listing = Listing::create([
                'customer_id' => $customer->customer_id,
                'status' => fake()->numberBetween(0, 1),
                'notes' => fake()->optional(0.75)->sentence(),
            ]);

            $productName = $this->productName($category->category_title, $index);

            $product = Product::create([
                'product_name' => $productName,
                'url_key' => $this->uniqueProductUrlKey($productName),
                'sku' => 'SEED-'.str_pad((string) $index, 5, '0', STR_PAD_LEFT),
                'description' => fake()->paragraph(),
                'category_id' => $category->category_id,
                'status' => true,
                'condition' => $condition->condition_id,
            ]);

            ListingItem::create([
                'listing_id' => $listing->listing_id,
                'product_id' => $product->product_id,
            ]);
        }
    }

    private function productName(string $categoryTitle, int $index): string
    {
        $productsByCategory = [
            'baby clothes' => [
                'Cotton Baby Sleepsuit',
                'Newborn Bodysuit Set',
                'Baby Romper',
                'Fleece Baby Sleepsack',
                'Infant Cardigan',
                'Baby Vest Multipack',
            ],
            'shoes' => [
                'Toddler Trainers',
                'Kids School Shoes',
                'Baby Pram Shoes',
                'Children Rain Boots',
                'Canvas Slip-on Shoes',
                'First Walker Shoes',
            ],
            'coats' => [
                'Kids Padded Winter Coat',
                'Children Rain Jacket',
                'Toddler Fleece Jacket',
                'Baby Snowsuit',
                'Hooded Parka',
                'Lightweight Puffer Jacket',
            ],
            'lego' => [
                'LEGO City Fire Truck Set',
                'LEGO Friends Cafe Set',
                'LEGO Classic Brick Box',
                'LEGO Duplo Animal Train',
                'LEGO Technic Race Car',
                'LEGO Creator Dinosaur Set',
            ],
            'dolls' => [
                'Soft Baby Doll',
                'Doll Pram',
                'Wooden Doll House',
                'Fashion Doll Set',
                'Doll Clothes Bundle',
                'Interactive Talking Doll',
            ],
            'puzzles' => [
                'Wooden Alphabet Puzzle',
                'Animal Jigsaw Puzzle',
                'Floor Puzzle Map',
                'Number Matching Puzzle',
                'Chunky Toddler Puzzle',
                '3D Dinosaur Puzzle',
            ],
            'games' => [
                'Junior Board Game',
                'Memory Card Game',
                'Kids Charades Game',
                'Magnetic Travel Game',
                'Family Dice Game',
                'Matching Picture Game',
            ],
            'story books' => [
                'Bedtime Story Collection',
                'Picture Book Bundle',
                'First Readers Set',
                'Fairy Tale Treasury',
                'Animal Stories Book',
                'Hardback Storybook',
            ],
            'learning books' => [
                'Phonics Workbook',
                'Early Maths Activity Book',
                'Alphabet Practice Book',
                'Preschool Learning Pack',
                'Handwriting Workbook',
                'Science Activity Book',
            ],
            'stroller' => [
                'Compact Travel Stroller',
                'Lightweight Pushchair',
                'Double Baby Stroller',
                'Stroller Rain Cover',
                'Pram Footmuff',
                'Travel System Stroller',
            ],
            'feeding items' => [
                'Baby Bottle Set',
                'High Chair',
                'Steriliser Kit',
                'Weaning Bowl Set',
                'Sippy Cup Bundle',
                'Baby Food Warmer',
            ],
            'carriers' => [
                'Ergonomic Baby Carrier',
                'Soft Baby Wrap',
                'Toddler Hip Seat',
                'Ring Sling Carrier',
                'Front Baby Carrier',
                'Structured Back Carrier',
            ],
            'bikes' => [
                'Kids Balance Bike',
                'Children Mountain Bike',
                'Toddler Tricycle',
                'Bike Helmet',
                'Stabiliser Wheel Set',
                'Junior BMX Bike',
            ],
            'scooters' => [
                'Three Wheel Scooter',
                'Foldable Kids Scooter',
                'Light Up Wheel Scooter',
                'Toddler Scooter',
                'Adjustable Kick Scooter',
                'Scooter Helmet Set',
            ],
            'sports toys' => [
                'Junior Football Goal',
                'Kids Basketball Hoop',
                'Soft Cricket Set',
                'Tennis Racket Set',
                'Garden Bowling Set',
                'Foam Ball Pack',
            ],
        ];

        $categoryKey = Str::lower($categoryTitle);
        $productNames = $productsByCategory[$categoryKey] ?? [Str::title($categoryTitle).' Item'];

        return fake()->randomElement($productNames).' '.$index;
    }

    private function uniqueProductUrlKey(string $productName): string
    {
        $baseUrlKey = Str::slug($productName);
        $baseUrlKey = $baseUrlKey !== '' ? $baseUrlKey : 'product';
        $urlKey = $baseUrlKey;
        $suffix = 2;

        while (Product::query()->where('url_key', $urlKey)->exists()) {
            $urlKey = $baseUrlKey.'-'.$suffix;
            $suffix++;
        }

        return $urlKey;
    }
}
