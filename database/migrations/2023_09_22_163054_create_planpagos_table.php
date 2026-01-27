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
            // CAMBIO: Totales a Decimal
            $table->decimal('total_pagar', 12, 2);
            $table->integer('estado')->default(1);
            $table->integer('desembolso')->default(1);
            $table->integer('pago_administrativo')->default(1);
            
            $table->foreignId('id_solicitud')->constrained('solicitud');

            $table->bigInteger('id_plan_aux')->nullable()->default(0);
            $table->string('moneda')->default('');
            $table->string('lapso_capital')->default('');
            $table->integer('nro_cuotas')->default(0);
            
            // Corregido: Tasa también debe ser decimal aquí para consistencia
            $table->decimal('tasa', 5, 2)->default(0); 
            
            $table->date('fecha_ultima_amortizacion')->nullable();
            $table->decimal('saldo_pendiente', 15, 2)->nullable(); // Un poco más grande por si acumula
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
