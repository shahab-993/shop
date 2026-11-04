<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE supplier_payments
                ADD CONSTRAINT chk_supplier_payment_source
                CHECK (
                    (purchase_id IS NOT NULL AND opening_balance_id IS NULL)
                    OR
                    (purchase_id IS NULL AND opening_balance_id IS NOT NULL)
                )
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE supplier_payments
                DROP CHECK chk_supplier_payment_source
            ");
        }
    }
};
