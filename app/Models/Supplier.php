<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'photo',
    'email',
    'phone',
    'address',
    'note',
    'opening_balance_amount',
    'opening_balance_date',
    'status',
    'created_by',
    'updated_by',
    'deleted',
    'deleted_at',
    'deleted_by',
])]
class Supplier extends Model
{
    protected function casts(): array
    {
        return [
            'opening_balance_amount' => 'decimal:2',
            'opening_balance_date' => 'date:Y-m-d',
            'status' => 'integer',
            'deleted' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }
}
