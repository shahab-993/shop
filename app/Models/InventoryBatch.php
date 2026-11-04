<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryBatch extends Model
{
   protected $fillable = [
    'product_id',
    'source_type',
    'source_id',
    'source_item_id',
    'received_at',
    'original_quantity',
    'remaining_quantity',
    'unit_cost',
    'original_cost',
    'remaining_cost',
];

protected function casts(): array
{
    return [
        'received_at' => 'datetime',
        'original_quantity' => 'decimal:3',
        'remaining_quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'original_cost' => 'decimal:2',
        'remaining_cost' => 'decimal:2',
    ];
}

    // Product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Purchase Item
    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseItem::class,
            'source_item_id'
        )->where('source_type', 'purchase');
    }

    // Sale Item Allocations
    public function allocations(): HasMany
    {
        return $this->hasMany(SaleItemAllocation::class);
    }

    // Stock Adjustments
    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }
}
