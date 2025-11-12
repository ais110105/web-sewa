<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories
        $electronics = Category::where('name', 'Electronics')->first();
        $vehicles = Category::where('name', 'Vehicles')->first();
        $tools = Category::where('name', 'Tools')->first();
        $eventEquipment = Category::where('name', 'Event Equipment')->first();
        $sportsEquipment = Category::where('name', 'Sports Equipment')->first();

        $items = [
            // Electronics
            [
                'category_id' => $electronics?->id,
                'name' => 'Canon EOS 5D Camera',
                'description' => 'Professional DSLR camera with full frame sensor',
                'photo_url' => null,
                'status' => 'available',
                'price_per_period' => 150000,
                'stock' => 5,
                'available_stock' => 5,
            ],
            [
                'category_id' => $electronics?->id,
                'name' => 'Sony A7 III Camera',
                'description' => 'Mirrorless camera with excellent low-light performance',
                'photo_url' => null,
                'status' => 'available',
                'price_per_period' => 200000,
                'stock' => 3,
                'available_stock' => 3,
            ],
            [
                'category_id' => $electronics?->id,
                'name' => 'MacBook Pro 16"',
                'description' => 'High-performance laptop for professional work',
                'photo_url' => null,
                'status' => 'available',
                'price_per_period' => 300000,
                'stock' => 10,
                'available_stock' => 10,
            ],

            // Vehicles
            [
                'category_id' => $vehicles?->id,
                'name' => 'Honda Beat 2023',
                'description' => 'Fuel-efficient scooter for city commuting',
                'photo_url' => null,
                'status' => 'available',
                'price_per_period' => 75000,
                'stock' => 15,
                'available_stock' => 15,
            ],
            [
                'category_id' => $vehicles?->id,
                'name' => 'Toyota Avanza 2022',
                'description' => '7-seater MPV perfect for family trips',
                'photo_url' => null,
                'status' => 'available',
                'price_per_period' => 350000,
                'stock' => 8,
                'available_stock' => 8,
            ],

            // Tools
            [
                'category_id' => $tools?->id,
                'name' => 'Bosch Drill Machine',
                'description' => 'Powerful electric drill for construction work',
                'photo_url' => null,
                'status' => 'available',
                'price_per_period' => 50000,
                'stock' => 20,
                'available_stock' => 20,
            ],
            [
                'category_id' => $tools?->id,
                'name' => 'Extension Ladder 6m',
                'description' => 'Aluminum extension ladder for high-reach work',
                'photo_url' => null,
                'status' => 'available',
                'price_per_period' => 40000,
                'stock' => 12,
                'available_stock' => 12,
            ],

            // Event Equipment
            [
                'category_id' => $eventEquipment?->id,
                'name' => 'Sound System Package',
                'description' => 'Complete sound system with speakers and mixer',
                'photo_url' => null,
                'status' => 'available',
                'price_per_period' => 500000,
                'stock' => 4,
                'available_stock' => 4,
            ],
            [
                'category_id' => $eventEquipment?->id,
                'name' => 'LED Projector',
                'description' => 'High-brightness projector for presentations',
                'photo_url' => null,
                'status' => 'available',
                'price_per_period' => 250000,
                'stock' => 6,
                'available_stock' => 6,
            ],
            [
                'category_id' => $eventEquipment?->id,
                'name' => 'Party Tent 6x6m',
                'description' => 'Waterproof tent for outdoor events',
                'photo_url' => null,
                'status' => 'available',
                'price_per_period' => 400000,
                'stock' => 5,
                'available_stock' => 5,
            ],

            // Sports Equipment
            [
                'category_id' => $sportsEquipment?->id,
                'name' => 'Mountain Bike',
                'description' => 'Full suspension mountain bike for trails',
                'photo_url' => null,
                'status' => 'available',
                'price_per_period' => 100000,
                'stock' => 10,
                'available_stock' => 10,
            ],
            [
                'category_id' => $sportsEquipment?->id,
                'name' => 'Camping Tent for 4',
                'description' => 'Waterproof camping tent with easy setup',
                'photo_url' => null,
                'status' => 'available',
                'price_per_period' => 80000,
                'stock' => 8,
                'available_stock' => 8,
            ],
        ];

        foreach ($items as $item) {
            if ($item['category_id']) {
                Item::firstOrCreate(
                    ['name' => $item['name']],
                    $item
                );
            }
        }
    }
}
