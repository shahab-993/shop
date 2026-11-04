<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'unit_name',
        'quantity',
        'unit_price',
        'discount_amount',
        'line_total',
        'cost_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
            'cost_total' => 'decimal:2',
        ];
    }

    // Sale
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    // Product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // FIFO Allocations
    public function allocations(): HasMany
    {
        return $this->hasMany(SaleItemAllocation::class);
    }
}
