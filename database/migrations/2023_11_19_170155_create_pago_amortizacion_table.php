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
            $table->float('monto_pago');
            $table->float('capital_pagado');
            $table->float('interes_pagado');
            $table->float('multa_pagada');
            $table->float('saldo_pendiente');
            $table->integer('numero_cuota');
            $table->float('total_seguro')->default(0);
            $table->string('forma_pago');
            $table->foreignId('id_caja');
            $table->foreign('id_caja')->references('id')->on('caja');
            $table->foreignId('id_plan_pago');
            $table->foreign('id_plan_pago')->references('id')->on('plan_pago');
            $table->foreignId('id_cuota')->nullable();
            $table->foreign('id_cuota')->references('id')->on('cuota');

            $table->integer('estado')->default(0);
            $table->decimal('monto_desembolso')->default(0);
            $table->string('tipo')->default('amortizacion');
            $table->date('fecha_desembolso')->nullable();


       
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
