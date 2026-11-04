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
        Schema::create('sale_item_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_item_id')
                ->constrained('sale_items')
                ->restrictOnDelete();

            $table->foreignId('inventory_batch_id')
                ->constrained('inventory_batches')
                ->restrictOnDelete();

            $table->decimal('quantity', 12, 3);

            $table->decimal('unit_cost', 12, 2);

            $table->decimal('total_cost', 12, 2);

            $table->timestamps();

            $table->index('inventory_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_item_allocations');
    }
};
