<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_detail_number', 'order_id', 'product_id', 'purchase_detail_id',
    'regular_price', 'sale_price', 'order_qty', 'discount_amount', 'total_amount',
    'status', 'created_by', 'updated_by', 'deleted', 'deleted_at', 'deleted_by',
])]
class OrderDetail extends Model
{
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseDetail(): BelongsTo
    {
        return $this->belongsTo(PurchaseDetail::class);
    }

    protected function casts(): array
    {
        return [
            'order_id' => 'integer', 'product_id' => 'integer', 'purchase_detail_id' => 'integer',
            'regular_price' => 'decimal:2', 'sale_price' => 'decimal:2',
            'order_qty' => 'integer', 'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2', 'status' => 'integer',
            'deleted' => 'integer', 'deleted_at' => 'datetime',
        ];
    }
}
