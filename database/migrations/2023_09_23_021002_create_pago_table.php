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
            $table->date('fecha_pago');
            
            // CAMBIO: Pagos y multas a Decimal
            $table->decimal('monto_pago', 12, 2);
            $table->integer('estado')->default(1);
            $table->integer('dias_retrasados')->nullable();
            $table->decimal('multa_dia', 12, 2)->nullable();
            $table->decimal('multa_total', 12, 2)->nullable();
            $table->decimal('monto_condonado', 12, 2)->default(0.00);
            
            $table->string('motivo_condonacion')->default('');
            $table->string('forma_pago');
            $table->string('imagen')->default('');
            
            $table->foreignId('id_usuario')->constrained('users');
            $table->foreignId('id_cuota')->constrained('cuota');
            $table->foreignId('id_caja')->constrained('caja');

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
