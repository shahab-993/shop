<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('username', 100)
                ->unique()
                ->after('name');

            $table->string('locale', 5)
                ->default('ps')
                ->after('password');

            $table->boolean('is_active')
                ->default(true)
                ->after('locale');

            $table->string('email')
                ->nullable()
                ->change();
        });
    }

  public function down(): void
{
    Schema::table('users', function (Blueprint $table) {

        $table->dropUnique(['username']);

        $table->dropColumn([
            'username',
            'locale',
            'is_active',
        ]);

        $table->string('email')->nullable(false)->change();
    });
}
};