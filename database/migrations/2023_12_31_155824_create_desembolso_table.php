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
        Schema::create('desembolso', function (Blueprint $table) {
            $table->id();
            
            // Cambiado a decimal para cálculos exactos
            $table->decimal('monto', 12, 2);
            
            // Usamos useCurrent() para que la BD asigne la fecha/hora actual por defecto
            $table->datetime('fecha')->useCurrent();
            
            $table->integer('estado')->default(0);
            
            // Definición estructurada y segura de llaves foráneas
            $table->unsignedBigInteger('id_plan_pago');
            $table->foreign('id_plan_pago')->references('id')->on('plan_pago');
            
            $table->unsignedBigInteger('id_usuario');
            $table->foreign('id_usuario')->references('id')->on('users');
            
            $table->unsignedBigInteger('id_caja');
            $table->foreign('id_caja')->references('id')->on('caja');
            
            // Nota: No se agregan $table->timestamps() porque tu SQL no los tiene.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desembolso');
    }
};
