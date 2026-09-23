<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueDeliveryChargeTitle implements ValidationRule
{
    public function __construct(private readonly ?int $ignoreId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table('delivery_charges')
            ->where('deleted', 0)
            ->whereRaw('LOWER(title) = ?', [mb_strtolower((string) $value)]);

        if ($this->ignoreId !== null) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail('The title has already been taken.');
        }
    }
}
