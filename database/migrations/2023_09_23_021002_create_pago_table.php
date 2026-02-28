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
        Schema::create('pago', function (Blueprint $table) {
            $table->id();
            
            // Columna agregada según tu SQL
            $table->string('codigo_transaccion', 50)->nullable()->index();
            
            $table->date('fecha_pago');
            
            // Manteniendo TODO el dinero como decimal para precisión financiera
            $table->decimal('monto_pago', 12, 2);
            $table->integer('estado')->default(1);
            $table->integer('dias_retrasados')->nullable();
            $table->decimal('multa_dia', 12, 2)->nullable();
            $table->decimal('multa_total', 12, 2)->nullable();
            $table->decimal('monto_condonado', 12, 2)->default(0.00);
            
            $table->string('motivo_condonacion')->default('');
            $table->string('forma_pago');
            $table->string('imagen')->default('');
            
            // Llaves foráneas estructuradas de forma segura
            $table->unsignedBigInteger('id_usuario');
            $table->foreign('id_usuario')->references('id')->on('users');
            
            $table->unsignedBigInteger('id_cuota');
            $table->foreign('id_cuota')->references('id')->on('cuota');
            
            $table->unsignedBigInteger('id_caja');
            $table->foreign('id_caja')->references('id')->on('caja');

            $table->decimal('monto_cuota', 10, 2)->nullable()->default(0.00);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago');
    }
};
