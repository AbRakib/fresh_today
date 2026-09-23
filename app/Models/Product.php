<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'subcategory_id',
    'name',
    'slug',
    'sku',
    'thumbnail',
    'short_description',
    'description',
    'weight',
    'gross_weight',
    'unit_id',
    'regular_price',
    'sale_price',
    'discount_percentage',
    'badge',
    'stock_quantity',
    'minimum_order_quantity',
    'is_featured',
    'status',
    'created_by',
    'updated_by',
    'deleted',
    'deleted_at',
    'deleted_by',
])]
class Product extends Model
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function measurementUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function purchaseDetails(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'subcategory_id' => 'integer',
            'unit_id' => 'integer',
            'regular_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'stock_quantity' => 'integer',
            'minimum_order_quantity' => 'integer',
            'is_featured' => 'integer',
            'status' => 'integer',
            'deleted' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }
}
