<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_number',
        'buyer_name',
        'buyer_phone',
        'sale_date',
        'subtotal',
        'discount_amount',
        'total_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    // User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Sale Items
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    // Sale Payments
    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }
}
