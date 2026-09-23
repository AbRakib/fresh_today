<?php

namespace App\Support;

use App\Models\Country;
use App\Models\Setting;

class Currency
{
    /**
     * @return array{country_id: int|null, country_name: string|null, code: string, symbol: string, digits: int}
     */
    public static function current(): array
    {
        $setting = Setting::query()
            ->where('deleted', 0)
            ->first(['country_id']);

        $country = $setting?->country_id
            ? Country::query()
                ->whereKey($setting->country_id)
                ->where('deleted', 0)
                ->first(['id', 'name', 'currency', 'currency_symbol', 'default_currency_digit'])
            : null;

        return [
            'country_id' => $country?->id,
            'country_name' => $country?->name,
            'code' => $country?->currency ?: 'BDT',
            'symbol' => $country?->currency_symbol ?: '৳',
            'digits' => (int) ($country?->default_currency_digit ?? 2),
        ];
    }

    public static function format(float|int|string|null $amount, ?array $currency = null): string
    {
        $currency ??= self::current();
        $digits = max(0, (int) ($currency['digits'] ?? 2));

        return number_format((float) ($amount ?? 0), $digits).' '.($currency['symbol'] ?? '৳');
    }
}
