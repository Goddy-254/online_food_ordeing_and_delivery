<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        // Get category records from the database
        $pizza = Category::where('slug', 'pizza')->firstOrFail();
        $burgers = Category::where('slug', 'burgers')->firstOrFail();
        $drinks = Category::where('slug', 'drinks')->firstOrFail();
        $chicken = Category::where('slug', 'chicken')->firstOrFail();

        // Sample menu items
        $menuItems = [

            [
                'category_id' => $pizza->id,
                'name' => 'Chicken Pizza',
                'price' => 850,
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=800&q=80',
            ],

            [
                'category_id' => $pizza->id,
                'name' => 'Beef Pizza',
                'price' => 950,
                'image' => 'https://images.unsplash.com/photo-1571407970349-bc81e7e96d47?auto=format&fit=crop&w=800&q=80',
            ],

            [
                'category_id' => $burgers->id,
                'name' => 'Cheese Burger',
                'price' => 500,
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=800&q=80',
            ],

            [
                'category_id' => $burgers->id,
                'name' => 'Chicken Burger',
                'price' => 600,
                'image' => 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?auto=format&fit=crop&w=800&q=80',
            ],

            [
                'category_id' => $drinks->id,
                'name' => 'Fresh Orange Juice',
                'price' => 200,
                'image' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?auto=format&fit=crop&w=800&q=80',
            ],

            [
                'category_id' => $chicken->id,
                'name' => 'Fried Chicken',
                'price' => 450,
                'image' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=800&q=80',
            ],

        ];

        // Insert or update menu items
        foreach ($menuItems as $item) {

            MenuItem::updateOrCreate(
                [
                    'category_id' => $item['category_id'],
                    'name' => $item['name'],
                ],
                $item
            );

        }
    }
}
