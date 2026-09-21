<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'country_id',
    'name',
    'phone_code',
    'currency_name',
    'currency',
    'currency_symbol',
    'default_currency_digit',
    'status',
    'created_by',
    'updated_by',
    'deleted',
    'deleted_at',
    'deleted_by',
])]
class Country extends Model
{
    protected function casts(): array
    {
        return [
            'default_currency_digit' => 'integer',
            'status' => 'integer',
            'deleted' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }
}
