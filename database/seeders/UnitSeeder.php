<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true],
            ['name' => 'Kilograms', 'short_name' => 'kg', 'is_active' => true],
            ['name' => 'Grams', 'short_name' => 'g', 'is_active' => true],
            ['name' => 'Liters', 'short_name' => 'L', 'is_active' => true],
            ['name' => 'Milliliters', 'short_name' => 'mL', 'is_active' => true],
            ['name' => 'Boxes', 'short_name' => 'box', 'is_active' => true],
            ['name' => 'Packets', 'short_name' => 'pkt', 'is_active' => true],
            ['name' => 'Sets', 'short_name' => 'set', 'is_active' => true],
            ['name' => 'Pairs', 'short_name' => 'pair', 'is_active' => true],
            ['name' => 'Meters', 'short_name' => 'mtr', 'is_active' => true],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['short_name' => $unit['short_name']],
                $unit
            );
        }
    }
}
