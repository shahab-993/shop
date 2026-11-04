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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained('sales')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->string('product_name', 150);
            $table->string('unit_name', 100);

            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 2);

            $table->decimal('discount_amount', 12, 2)->default(0);

            $table->decimal('line_total', 12, 2);

            $table->decimal('cost_total', 12, 2)->default(0);

            $table->timestamps();

            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
