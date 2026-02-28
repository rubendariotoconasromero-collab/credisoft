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
        Schema::create('pago_amortizacion', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            
            // Cambiado a decimal para máxima precisión en el manejo de dinero
            $table->decimal('monto_pago', 12, 2);
            $table->decimal('capital_pagado', 12, 2);
            $table->decimal('interes_pagado', 12, 2);
            $table->decimal('multa_pagada', 12, 2);
            $table->decimal('saldo_pendiente', 12, 2);
            
            $table->string('forma_pago');
            
            // Definición estructurada de llaves foráneas
            $table->unsignedBigInteger('id_caja');
            $table->foreign('id_caja')->references('id')->on('caja');
            
            $table->unsignedBigInteger('id_plan_pago');
            $table->foreign('id_plan_pago')->references('id')->on('plan_pago');
            
            $table->unsignedBigInteger('id_cuota'); // En tu SQL NO es nullable
            $table->foreign('id_cuota')->references('id')->on('cuota');

            // Campo extraído de tu SQL
            $table->integer('id_plan_ligado')->nullable(); 
            
            $table->decimal('total_seguro', 12, 2)->nullable()->default(0); // Convertido a decimal también
            $table->integer('estado')->nullable()->default(0);
            $table->decimal('monto_desembolso', 10, 2)->nullable()->default(0.00);
            $table->string('tipo')->nullable()->default('amortizacion');
            $table->date('fecha_desembolso')->nullable();
            $table->integer('numero_cuota')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_amortizacion');
    }
};
