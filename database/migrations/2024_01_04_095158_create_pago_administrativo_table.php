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
        Schema::create('pago_administrativo', function (Blueprint $table) {
            $table->id();
            
            // Cambiado a decimal para cálculos exactos de dinero
            $table->decimal('monto', 12, 2);
            
            // Usamos useCurrent() para asignar la fecha/hora actual por defecto
            $table->datetime('fecha')->useCurrent();
            
            $table->integer('estado')->default(0);
            $table->string('descripcion');

            // Definición estructurada de llaves foráneas
            $table->unsignedBigInteger('id_plan_pago');
            $table->foreign('id_plan_pago')->references('id')->on('plan_pago');
            
            $table->unsignedBigInteger('id_usuario');
            $table->foreign('id_usuario')->references('id')->on('users');
            
            $table->unsignedBigInteger('id_caja');
            $table->foreign('id_caja')->references('id')->on('caja');
            
            // Se omiten los timestamps tal como está en tu base de datos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_administrativo');
    }
};
