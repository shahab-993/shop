<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'user_id',
        'invoice_number',
        'supplier_invoice_number',
        'purchase_date',
        'subtotal',
        'discount_amount',
        'total_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    // Supplier
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Purchase Items
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Supplier Payments
    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
