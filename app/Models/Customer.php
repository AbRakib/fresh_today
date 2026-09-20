<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'phone',
    'profile_image',
    'address',
    'password',
    'gender',
    'date_of_birth',
    'status',
    'created_by',
    'updated_by',
    'deleted',
    'deleted_at',
    'deleted_by',
])]
#[Hidden(['password'])]
class Customer extends Model
{
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'date_of_birth' => 'date:Y-m-d',
            'status' => 'integer',
            'deleted' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }
}
