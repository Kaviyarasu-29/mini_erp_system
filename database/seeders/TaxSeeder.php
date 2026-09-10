<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = [
            ['name' => 'Exempt (0%)', 'rate' => 0.00, 'is_active' => true],
            ['name' => 'GST 3%', 'rate' => 3.00, 'is_active' => true],
            ['name' => 'GST 5%', 'rate' => 5.00, 'is_active' => true],
            ['name' => 'GST 12%', 'rate' => 12.00, 'is_active' => true],
            ['name' => 'GST 18%', 'rate' => 18.00, 'is_active' => true],
            ['name' => 'GST 28%', 'rate' => 28.00, 'is_active' => true],
        ];

        foreach ($taxes as $tax) {
            Tax::updateOrCreate(
                ['name' => $tax['name']],
                $tax
            );
        }
    }
}
