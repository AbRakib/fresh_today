<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::updateOrCreate(
            [
                'email' => 'walk-in-customer@freshtoday.com',
                'deleted' => 0,
            ],
            [
                'name' => 'walk-in-customer',
                'password' => 'password',
                'status' => 1,
            ],
        );
    }
}
