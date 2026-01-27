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
        
        Schema::create('solicitud', function (Blueprint $table) {
            $table->id();
            // CAMBIO: Importes y Tasas a Decimal
            $table->decimal('importe_solicitud', 12, 2);
            $table->string('moneda');
            $table->string('lapso_capital');
            $table->integer('nro_cuotas');
            
            // Tasa: (5,2) permite ej: 12.50, 100.00
            $table->decimal('tasa', 5, 2);
            
            $table->date('fecha')->nullable();
            $table->date('fecha_desembolso');
            $table->date('fecha_primera_cuota');
            $table->string('destino_prestamo');
            $table->string('tipo_garantia');
            $table->string('tipo_desembolso');
            $table->integer('estado')->default(1);// 1 nuevo -> 2 -> aprobado -> 3 -> anulado
            
            // Asumiendo tabla 'cliente' existe
            $table->unsignedBigInteger('id_cliente');
            // $table->foreign('id_cliente')->references('id')->on('cliente');

            $table->foreignId('id_usuario')->constrained('users');

            $table->decimal('monto_pago_adm', 10, 2)->nullable()->default(0.00);
            $table->string('tipo_tasa', 50)->nullable()->default('amortizable');
            $table->string('observacion', 250)->nullable()->default('');
            $table->string('tipo_solicitud', 250)->nullable()->default('Nuevo');
            $table->integer('cantidad_reprogramaciones')->nullable()->default(0);
            $table->integer('cantidad_refinanciamientos')->nullable()->default(0);
            
            // Refinanciamiento también es dinero
            $table->decimal('monto_refinanciamiento', 12, 2)->nullable()->default(0);
            $table->integer('desembolso')->nullable()->default(0);
            
            $table->unsignedBigInteger('id_solicitud_origen')->nullable();
            $table->foreign('id_solicitud_origen')->references('id')->on('solicitud')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud');
    }
};
