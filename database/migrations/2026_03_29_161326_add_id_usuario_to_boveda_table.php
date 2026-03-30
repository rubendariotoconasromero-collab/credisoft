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
        Schema::table('boveda', function (Blueprint $table) {
            // Agregamos la llave foránea apuntando a 'users'
            $table->foreignId('id_usuario')->nullable()->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('boveda', function (Blueprint $table) {
            $table->dropForeign(['id_usuario']);
            $table->dropColumn('id_usuario');
        });
    }
};
