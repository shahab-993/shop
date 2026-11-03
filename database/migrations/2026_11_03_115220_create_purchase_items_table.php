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
    Schema::create('purchase_items', function (Blueprint $table) {
        $table->id();

        $table->foreignId('purchase_id')
            ->constrained('purchases')
            ->cascadeOnDelete();

        $table->foreignId('product_id')
            ->constrained('products')
            ->restrictOnDelete();

        // د محصول نوم او واحد د پېرود پر وخت ثبتول
        $table->string('product_name', 150);
        $table->string('unit_name', 100);

        $table->decimal('quantity', 12, 3);
        $table->decimal('unit_cost', 12, 2);

        $table->decimal('discount_amount', 12, 2)->default(0);
        $table->decimal('line_total', 12, 2);

        $table->timestamps();

        $table->index('product_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
