<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_name',
    'email',
    'phone',
    'address',
    'logo',
    'meta_icon',
    'status',
    'created_by',
    'updated_by',
    'deleted',
    'deleted_at',
    'deleted_by',
])]
class Setting extends Model
{
    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }
}
