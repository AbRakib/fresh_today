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
            ['name' => 'Kilogram', 'short_name' => 'kg'],
            ['name' => 'Gram', 'short_name' => 'g'],
            ['name' => 'Liter', 'short_name' => 'L'],
            ['name' => 'Milliliter', 'short_name' => 'mL'],
            ['name' => 'Meter', 'short_name' => 'm'],
            ['name' => 'Foot', 'short_name' => 'ft'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                [
                    'name' => $unit['name'],
                    'deleted' => 0,
                ],
                [
                    'short_name' => $unit['short_name'],
                    'default' => $unit['default'] ?? 0,
                    'status' => 1,
                ],
            );
        }
    }
}
