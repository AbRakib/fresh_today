<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::updateOrCreate(
            [
                'name' => 'walk-in-supplier',
                'deleted' => 0,
            ],
            [
                'status' => 1,
            ],
        );
    }
}
