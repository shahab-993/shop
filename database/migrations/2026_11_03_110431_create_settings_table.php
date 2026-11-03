
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {

            $table->id();

            $table->string('shop_name', 150);

            $table->string('phone', 30)->nullable();

            $table->string('email', 150)->nullable();

            $table->text('address')->nullable();

            $table->string('logo_path')->nullable();

            $table->string('currency', 3)->default('AFN');

            $table->string('default_locale', 5)->default('ps');

            $table->text('invoice_footer')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
