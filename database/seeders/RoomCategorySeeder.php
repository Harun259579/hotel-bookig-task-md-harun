<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomCategory;

class RoomCategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Premium Deluxe', 'base_price' => 12000],
            ['name' => 'Super Deluxe',   'base_price' => 10000],
            ['name' => 'Standard Deluxe','base_price' => 8000],
        ];

        foreach ($data as $row) {
            RoomCategory::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}
