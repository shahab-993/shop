<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItemAllocation extends Model
{
    protected $fillable = [
        'sale_item_id',
        'inventory_batch_id',
        'quantity',
        'unit_cost',
        'total_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    // Sale Item
    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    // Inventory Batch
    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class);
    }
}
