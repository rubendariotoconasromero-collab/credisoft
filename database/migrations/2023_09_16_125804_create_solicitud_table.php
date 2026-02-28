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
            
            // Ajustado a double(8,2) según tu SQL
            $table->double('importe_solicitud', 8, 2); 
            $table->string('moneda');
            $table->string('lapso_capital');
            $table->integer('nro_cuotas');
            
            // Ajustado a decimal(11,2) según tu SQL
            $table->decimal('tasa', 11, 2);
            
            $table->date('fecha')->nullable();
            $table->date('fecha_desembolso');
            $table->date('fecha_primera_cuota');
            $table->string('destino_prestamo');
            $table->string('tipo_garantia');
            $table->string('tipo_desembolso');
            $table->integer('estado')->default(1);
            
            // Llave foránea para Cliente (descomentada y asegurada)
            $table->unsignedBigInteger('id_cliente');
            $table->foreign('id_cliente')->references('id')->on('cliente');

            $table->unsignedBigInteger('id_usuario');
            $table->foreign('id_usuario')->references('id')->on('users');

            $table->decimal('monto_pago_adm', 10, 2)->nullable()->default(0.00);
            $table->string('tipo_tasa', 50)->nullable()->default('amortizable');
            $table->string('observacion', 250)->nullable()->default('');
            $table->string('tipo_solicitud', 250)->nullable()->default('Nuevo');
            $table->integer('cantidad_reprogramaciones')->nullable()->default(0);
            $table->integer('cantidad_refinanciamientos')->nullable()->default(0);
            
            // Ajustado a integer según tu SQL (en la migración lo tenías como decimal)
            // Si manejas dinero aquí, te recomiendo cambiarlo a decimal(12,2) en la BD, 
            // pero para igualar tu SQL lo dejo como integer.
            $table->integer('monto_refinanciamiento')->nullable()->default(0); 
            
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
