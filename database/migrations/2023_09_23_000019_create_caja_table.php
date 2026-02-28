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
        Schema::create('caja', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fechahora_apertura');
            $table->dateTime('fechahora_cierre')->nullable();
            
            // ¡Cambiado a decimal(12, 2) para cálculos monetarios exactos!
            $table->decimal('monto_inicial', 12, 2);
            $table->decimal('monto_final', 12, 2)->nullable();
            $table->decimal('efectivo_total', 12, 2)->nullable();
            $table->decimal('deposito_total', 12, 2)->nullable();
            $table->decimal('efectivo_venta', 12, 2)->nullable();
            $table->decimal('deposito_venta', 12, 2)->nullable();
            $table->decimal('efectivo_gasto', 12, 2)->nullable();
            $table->decimal('deposito_gasto', 12, 2)->nullable();
            $table->decimal('total_ingreso', 12, 2)->nullable();
            $table->decimal('total_egreso', 12, 2)->nullable();
            $table->decimal('diferencia', 12, 2)->nullable();
            
            $table->integer('estado')->default(1);
            
            // Llave foránea
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
        Schema::dropIfExists('caja');
    }
};
