<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'transaction_no',
    'date',
    'account_id',
    'payment_type',
    'transaction_type',
    'reference_type',
    'reference_description',
    'description',
    'total_amount',
    'notes',
    'reviewed',
    'reviewed_at',
    'reviewed_by',
    'created_by',
    'updated_by',
    'deleted',
    'deleted_at',
    'deleted_by',
])]
class Transaction extends Model
{
    public function account(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'account_id');
    }

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'account_id' => 'integer',
            'payment_type' => 'integer',
            'transaction_type' => 'integer',
            'total_amount' => 'decimal:2',
            'reviewed' => 'integer',
            'reviewed_at' => 'datetime',
            'deleted' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }
}
