<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_number', 'customer_id', 'total_product', 'subtotal', 'discount_amount',
    'delivery_charge', 'total_amount', 'paid_amount', 'due_amount', 'payment_status',
    'order_date', 'payment_date', 'delivery_date', 'delivery_address', 'note',
    'order_status', 'created_by', 'updated_by', 'deleted', 'deleted_at', 'deleted_by',
])]
class Order extends Model
{
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2', 'discount_amount' => 'decimal:2',
            'delivery_charge' => 'decimal:2', 'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2', 'due_amount' => 'decimal:2',
            'payment_status' => 'integer', 'order_date' => 'date:Y-m-d',
            'payment_date' => 'date:Y-m-d', 'delivery_date' => 'date:Y-m-d',
            'order_status' => 'integer', 'deleted' => 'integer', 'deleted_at' => 'datetime',
        ];
    }
}
