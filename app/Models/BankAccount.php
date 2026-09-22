<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'slug',
    'account_number',
    'available_balance',
    'opening_balance',
    'opening_balance_date',
    'can_edit',
    'is_default',
    'created_by',
    'updated_by',
    'deleted',
    'deleted_at',
    'deleted_by',
])]
class BankAccount extends Model
{
    protected function casts(): array
    {
        return [
            'available_balance' => 'decimal:2',
            'opening_balance' => 'decimal:2',
            'opening_balance_date' => 'date:Y-m-d',
            'can_edit' => 'integer',
            'is_default' => 'integer',
            'deleted' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }
}
