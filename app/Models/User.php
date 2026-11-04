<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'username',
    'email',
    'password',
    'locale',
    'is_active',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Stock Adjustments
    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    // Purchases
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    // Supplier Payments
    public function supplierPayments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    // Supplier Opening Balances
    public function supplierOpeningBalances(): HasMany
    {
        return $this->hasMany(SupplierOpeningBalance::class);
    }

    // Sales
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    // Sale Payments
    public function salePayments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    // Expenses
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // Cash Transactions
    public function cashTransactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }
}
