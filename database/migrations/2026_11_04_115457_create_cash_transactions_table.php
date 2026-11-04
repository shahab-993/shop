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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('type', 30);

            $table->decimal('amount', 12, 2);

            $table->dateTime('transaction_date');

            $table->string('source_type', 30)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            $table->string('description', 255)->nullable();

            $table->string('status', 20)->default('completed');

            $table->timestamps();

            $table->index(['transaction_date', 'type']);
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
