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
        Schema::create('plan_pago_respaldo', function (Blueprint $table) {
            $table->id();

            // Datos del plan_pago original
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_ultima_amortizacion')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->float('total_pagar')->nullable();
            $table->decimal('saldo_pendiente', 12, 2)->default(0);
            $table->string('moneda')->nullable();
            $table->string('lapso_capital')->nullable();
            $table->integer('nro_cuotas')->nullable();
            $table->integer('tasa')->nullable();
            $table->string('tipo_tasa')->default('amortizable');
            $table->integer('estado')->default(1);
            $table->integer('desembolso')->default(1);
            $table->integer('pago_administrativo')->default(1);
            $table->unsignedBigInteger('id_solicitud')->nullable(); // relación al plan original
            $table->unsignedBigInteger('id_plan_aux')->default(0);

            // Relación con solicitud_respaldo
            $table->unsignedBigInteger('solicitud_respaldo_id');
            $table->foreign('solicitud_respaldo_id')->references('id')->on('solicitud_respaldo');

            // Información de auditoría
            $table->unsignedBigInteger('plan_pago_original_id'); // ID del plan_pago original
            $table->string('accion'); // create, update, delete
            $table->unsignedBigInteger('usuario_accion')->nullable();
            $table->foreign('usuario_accion')->references('id')->on('users')->onDelete('set null');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_pago_respaldo');
    }
};
