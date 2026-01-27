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
        Schema::create('desembolso', function (Blueprint $table) {
            $table->id();
            $table->float('monto');
            $table->datetime('fecha')->default(now());
            $table->integer('estado')->default(0);
            $table->foreignId('id_plan_pago');
            $table->foreign('id_plan_pago')->references('id')->on('plan_pago');
            $table->foreignId('id_usuario');
            $table->foreign('id_usuario')->references('id')->on('users');
            $table->foreignId('id_caja');
            $table->foreign('id_caja')->references('id')->on('caja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desembolso');
    }
};
