<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->foreignId('opening_balance_id')
                ->nullable()
                ->after('purchase_id')
                ->constrained('supplier_opening_balances')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropForeign(['opening_balance_id']);
            $table->dropColumn('opening_balance_id');
        });
    }
};
