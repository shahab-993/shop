<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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

    public function down(): void
    {
        DB::statement("
        ALTER TABLE supplier_payments
        DROP CHECK chk_supplier_payment_source
    ");
    }
};
