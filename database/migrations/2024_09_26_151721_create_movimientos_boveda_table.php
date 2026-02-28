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
            $table->string('tipo_movimiento'); // Ej: ingreso, salida
            
            // Subimos la precisión a 12,2 para mantener el estándar del proyecto
            $table->decimal('monto', 12, 2); 
            $table->string('descripcion');
            $table->datetime('fecha');

            // Definición estructurada de llaves foráneas (para proteger la integridad de datos)
            $table->unsignedBigInteger('id_boveda');
            $table->foreign('id_boveda')->references('id')->on('boveda');
            
            $table->unsignedBigInteger('id_usuario');
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
