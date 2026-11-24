<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $categories = [
            [
                'name' => 'Pizza',
                'description' => 'Delicious freshly prepared pizzas',
                'slug' => 'pizza',
            ],
            [
                'name' => 'Burgers',
                'description' => 'Fresh and tasty burgers',
                'slug' => 'burgers',
            ],
            [
                'name' => 'Drinks',
                'description' => 'Refreshing cold and hot drinks',
                'slug' => 'drinks',
            ],
            [
                'name' => 'Chicken',
                'description' => 'Delicious chicken meals',
                'slug' => 'chicken',
            ],
            [
                'name' => 'Snacks',
                'description' => 'Quick bites and snacks',
                'slug' => 'snacks',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
    }

