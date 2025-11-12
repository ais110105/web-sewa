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
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets',
            ],
            [
                'name' => 'Vehicles',
                'description' => 'Cars, motorcycles, and other vehicles',
            ],
            [
                'name' => 'Tools',
                'description' => 'Construction and household tools',
            ],
            [
                'name' => 'Event Equipment',
                'description' => 'Equipment for events and parties',
            ],
            [
                'name' => 'Sports Equipment',
                'description' => 'Sports and outdoor equipment',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
