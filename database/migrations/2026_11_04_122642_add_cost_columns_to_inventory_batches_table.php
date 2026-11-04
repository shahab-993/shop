<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->decimal('original_cost', 12, 2)
                ->default(0)
                ->after('unit_cost');

            $table->decimal('remaining_cost', 12, 2)
                ->default(0)
                ->after('original_cost');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropColumn([
                'original_cost',
                'remaining_cost',
            ]);
        });
    }
};