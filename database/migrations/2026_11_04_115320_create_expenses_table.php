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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('category', 100);

            $table->string('title', 150);

            $table->decimal('amount', 12, 2);

            $table->dateTime('expense_date');

            $table->string('payment_method', 20)->default('cash');

            $table->text('notes')->nullable();

            $table->string('status', 20)->default('completed');

            $table->timestamps();

            $table->index('expense_date');
            $table->index('category');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
