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
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->string('source_type', 30);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('source_item_id')->nullable();

            $table->dateTime('received_at');

            $table->decimal('original_quantity', 12, 3);
            $table->decimal('remaining_quantity', 12, 3);

            $table->decimal('unit_cost', 12, 2);

            $table->timestamps();

            $table->index(['product_id', 'received_at']);
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
