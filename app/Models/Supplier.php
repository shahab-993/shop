<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Supplier Opening Balance
    public function openingBalance(): HasOne
    {
        return $this->hasOne(SupplierOpeningBalance::class);
    }

    // Purchases
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    // Supplier Payments
    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
