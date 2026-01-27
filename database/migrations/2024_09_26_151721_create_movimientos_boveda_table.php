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
        Schema::create('movimientos_boveda', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_movimiento');
            $table->decimal('monto', 10, 2);
            $table->string('descripcion');
            $table->datetime('fecha');

            $table->foreignId('id_boveda');
            $table->foreign('id_boveda')->references('id')->on('boveda');
            
            $table->foreignId('id_usuario');
            $table->foreign('id_usuario')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_boveda');
    }
};
