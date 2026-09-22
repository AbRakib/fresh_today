<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bank_accounts')->updateOrInsert(
            ['slug' => 'cash-on-hand'],
            [
                'name' => 'Cash On Hand',
                'can_edit' => 0,
                'is_default' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
}
