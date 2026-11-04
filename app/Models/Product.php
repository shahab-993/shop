<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'unit_id',
        'sku',
        'barcode',
        'purchase_price',
        'selling_price',
        'min_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'min_stock' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    // Category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Unit
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    // Translations
    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    // Inventory Batches
    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    // Stock Adjustments
    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    // Purchase Items
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Sale Items
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
