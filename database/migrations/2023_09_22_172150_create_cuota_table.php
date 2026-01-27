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
        Schema::create('cuota', function (Blueprint $table) {
            $table->id();
            $table->integer('numero');
            $table->date('fecha');// FECHA PAGO
            
            // CAMBIO CRITICO: Todo a Decimal
            $table->decimal('capital', 12, 2);
            $table->decimal('interes', 12, 2);
            $table->decimal('saldo_capital', 12, 2);
            $table->decimal('ahorro', 12, 2)->nullable();
            $table->decimal('seguro', 12, 2)->nullable();
            $table->decimal('total', 12, 2);
            
            $table->integer('estado')->default(1);
            $table->integer('amortizado')->default(0);
            
            $table->foreignId('id_plan_pago')->constrained('plan_pago');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuota');
    }
};
