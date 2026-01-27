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
        Schema::create('solicitud_respaldo', function (Blueprint $table) {
            $table->id();

            // Datos principales de la solicitud original
            $table->float('importe_solicitud')->nullable();
            $table->string('moneda')->nullable();
            $table->string('lapso_capital')->nullable();
            $table->integer('nro_cuotas')->nullable();
            $table->integer('tasa')->nullable();
            $table->date('fecha')->nullable(); // Mantenemos nullable() por si no viene dato
            $table->date('fecha_desembolso')->nullable();
            $table->date('fecha_primera_cuota')->nullable();
            $table->string('destino_prestamo')->nullable();
            $table->string('tipo_garantia')->nullable();
            $table->string('tipo_desembolso')->nullable();
            $table->string('tipo_tasa')->default('amortizable');

            $table->decimal('monto_pago_adm', 10, 2)->default(0);
            $table->integer('estado')->default(1); // 1 nuevo -> 2 -> aprobado -> 3 -> anulado

            // Relaciones
            $table->unsignedBigInteger('id_cliente')->nullable();
            $table->foreign('id_cliente')->references('id')->on('cliente')->onDelete('set null');

            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('set null');

            // Información adicional del respaldo
            $table->unsignedBigInteger('solicitud_id'); // ID original de la solicitud
            $table->string('accion'); // Tipo de acción (insert, update, delete)
            $table->unsignedBigInteger('usuario_accion')->nullable(); // Usuario que realizó la acción
            $table->foreign('usuario_accion')->references('id')->on('users')->onDelete('set null');

            $table->timestamps(); // created_at y updated_at para el respaldo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_respaldo');
    }
};
