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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('inventory_batch_id')
                ->nullable()
                ->constrained('inventory_batches')
                ->restrictOnDelete();

            $table->string('type', 20);

            $table->decimal('quantity', 12, 3);

            $table->decimal('unit_cost', 12, 2)->nullable();

            $table->string('reason', 150);

            $table->dateTime('adjusted_at');

            $table->timestamps();

            $table->index(['product_id', 'adjusted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
