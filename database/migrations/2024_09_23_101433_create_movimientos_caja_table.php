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
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_movimiento'); // Ej: ingreso, egreso, apertura
            $table->string('descripcion');
            
            // Ajustado a 12,2 para mantener consistencia con tus otras tablas financieras
            $table->decimal('monto', 12, 2); 
            
            $table->datetime('fecha');
            
            // Definición estructurada de llaves foráneas (¡que faltaban en tu SQL original!)
            $table->unsignedBigInteger('id_caja');
            $table->foreign('id_caja')->references('id')->on('caja');
            
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
        Schema::dropIfExists('movimientos_caja');
    }
};
