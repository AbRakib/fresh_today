<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'image',
    'product_id',
    'button',
    'status',
    'created_by',
    'updated_by',
    'deleted',
    'deleted_at',
    'deleted_by',
])]
class Slider extends Model
{
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'status' => 'integer',
            'deleted' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }
}
