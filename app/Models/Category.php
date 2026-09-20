<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'icon',
    'status',
    'created_by',
    'updated_by',
    'deleted',
    'deleted_at',
    'deleted_by',
])]
class Category extends Model
{
    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
    }

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'deleted' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }
}
