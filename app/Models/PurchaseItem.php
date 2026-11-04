<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'product_name',
        'unit_name',
        'quantity',
        'unit_cost',
        'discount_amount',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    // Purchase
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    // Product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Inventory Batches
    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(
            InventoryBatch::class,
            'source_item_id'
        )->where('source_type', 'purchase');
    }
}
