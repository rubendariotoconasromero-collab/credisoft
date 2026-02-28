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
        Schema::create('plan_pago', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('total_pagar', 12, 2);
            $table->integer('estado')->default(1);
            $table->integer('desembolso')->default(1);
            $table->integer('pago_administrativo')->default(1);
            
            // Llave foránea estructurada de forma segura
            $table->unsignedBigInteger('id_solicitud');
            $table->foreign('id_solicitud')->references('id')->on('solicitud');

            // Campos extraídos de tu SQL
            $table->bigInteger('id_plan_aux')->nullable()->default(0);
            $table->string('moneda')->default('');
            $table->string('lapso_capital')->default('');
            $table->integer('nro_cuotas')->default(0);
            
            // REVERTIDO: Según tu SQL, la tasa aquí es entera
            $table->integer('tasa')->default(0); 
            
            $table->date('fecha_ultima_amortizacion')->nullable();
            $table->decimal('saldo_pendiente', 15, 2)->nullable();
            $table->date('fecha_registro')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_pago');
    }
};
