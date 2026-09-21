<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'purchase_detail_number',
    'purchase_id',
    'product_id',
    'purchase_price',
    'sell_price',
    'purchase_qty',
    'sell_qty',
    'available_qty',
    'total_amount',
    'expire_date',
    'receive_status',
    'status',
    'created_by',
    'updated_by',
    'deleted',
    'deleted_at',
    'deleted_by',
])]
class PurchaseDetail extends Model
{
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'purchase_id' => 'integer',
            'product_id' => 'integer',
            'purchase_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'purchase_qty' => 'integer',
            'sell_qty' => 'integer',
            'available_qty' => 'integer',
            'total_amount' => 'decimal:2',
            'expire_date' => 'date:Y-m-d',
            'receive_status' => 'integer',
            'status' => 'integer',
            'deleted' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }
}
