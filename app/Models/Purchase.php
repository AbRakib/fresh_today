<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'purchase_number',
    'supplier_id',
    'total_product',
    'subtotal',
    'paid_amount',
    'due_amount',
    'payment_status',
    'purchase_date',
    'payment_date',
    'note',
    'receive_status',
    'created_by',
    'updated_by',
    'deleted',
    'deleted_at',
    'deleted_by',
])]
class Purchase extends Model
{
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    protected function casts(): array
    {
        return [
            'supplier_id' => 'integer',
            'total_product' => 'integer',
            'subtotal' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
            'payment_status' => 'integer',
            'purchase_date' => 'date:Y-m-d',
            'payment_date' => 'date:Y-m-d',
            'receive_status' => 'integer',
            'deleted' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }
}
