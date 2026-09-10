<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesData = [
            'Electronics' => ['Mobile Phones', 'Laptops & Computers', 'Accessories', 'Audio & Headphones', 'Wearables'],
            'Clothing & Fashion' => ['Men\'s Wear', 'Women\'s Wear', 'Kids Wear', 'Footwear'],
            'Grocery & Staples' => ['Beverages', 'Snacks & Packaged Food', 'Grains & Pulses', 'Spices & Cooking Oil'],
            'Home & Kitchen' => ['Kitchen Appliances', 'Home Decor', 'Cookware'],
            'Office Supplies' => ['Stationery', 'Paper Products', 'Office Furniture'],
        ];

        foreach ($categoriesData as $parentName => $subcategories) {
            $parent = Category::firstOrCreate(
                ['name' => $parentName, 'parent_id' => null],
                ['is_active' => true]
            );

            foreach ($subcategories as $subName) {
                Category::firstOrCreate(
                    ['name' => $subName, 'parent_id' => $parent->id],
                    ['is_active' => true]
                );
            }
        }
    }
}
