<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed the application's default company settings.
     */
    public function run(): void
    {
        $countryId = Country::query()
            ->where('name', 'Bangladesh')
            ->where('deleted', 0)
            ->value('id');

        $setting = Setting::query()->where('deleted', 0)->firstOrNew();

        $setting->fill([
            'country_id' => $countryId,
            'company_name' => 'Fresh Today',
            'email' => 'info@freshtoday.com',
            'phone' => '+880',
            'address' => 'Dhaka, Bangladesh',
            'status' => 1,
            'deleted' => 0,
        ]);

        $setting->save();
    }
}
