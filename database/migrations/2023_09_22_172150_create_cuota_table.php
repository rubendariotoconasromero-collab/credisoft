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
            $table->date('fecha'); // FECHA PAGO
            
            // CORREGIDO: Ajustado a double(8,2) según tu SQL original
            $table->double('capital', 8, 2);
            $table->double('interes', 8, 2);
            $table->double('saldo_capital', 8, 2);
            $table->double('ahorro', 8, 2)->nullable();
            $table->double('seguro', 8, 2)->nullable();
            $table->double('total', 8, 2);
            
            $table->integer('estado')->default(1);
            $table->integer('amortizado')->default(0);
            
            // Definición consistente de la llave foránea
            $table->unsignedBigInteger('id_plan_pago');
            $table->foreign('id_plan_pago')->references('id')->on('plan_pago');

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
