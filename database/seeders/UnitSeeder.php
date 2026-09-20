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
            ['name' => 'Piece', 'short_name' => 'pc', 'default' => 1],
            ['name' => 'Kilogram', 'short_name' => 'kg'],
            ['name' => 'Gram', 'short_name' => 'g'],
            ['name' => 'Liter', 'short_name' => 'L'],
            ['name' => 'Milliliter', 'short_name' => 'mL'],
            ['name' => 'Dozen', 'short_name' => 'doz'],
            ['name' => 'Packet', 'short_name' => 'pkt'],
            ['name' => 'Box', 'short_name' => 'box'],
            ['name' => 'Bottle', 'short_name' => 'btl'],
            ['name' => 'Bag', 'short_name' => 'bag'],
            ['name' => 'Carton', 'short_name' => 'ctn'],
            ['name' => 'Meter', 'short_name' => 'm'],
            ['name' => 'Foot', 'short_name' => 'ft'],
            ['name' => 'Pair', 'short_name' => 'pair'],
            ['name' => 'Set', 'short_name' => 'set'],
            ['name' => 'Roll', 'short_name' => 'roll'],
            ['name' => 'Bundle', 'short_name' => 'bdl'],
            ['name' => 'Sack', 'short_name' => 'sack'],
            ['name' => 'Can', 'short_name' => 'can'],
            ['name' => 'Jar', 'short_name' => 'jar'],
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
