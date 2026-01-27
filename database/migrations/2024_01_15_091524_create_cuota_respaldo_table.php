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
        Schema::create('cuota_respaldo', function (Blueprint $table) {
            $table->id();

            // Datos de la cuota original
            $table->integer('numero')->nullable();
            $table->date('fecha')->nullable();
            $table->float('capital')->nullable();
            $table->float('interes')->nullable();
            $table->float('saldo_capital')->nullable();
            $table->float('ahorro')->nullable();
            $table->float('seguro')->nullable();
            $table->float('total')->nullable();
            $table->integer('estado')->default(1);
            $table->integer('amortizado')->default(0);

            // Relación con plan_pago_respaldo
            $table->unsignedBigInteger('plan_pago_respaldo_id');
            $table->foreign('plan_pago_respaldo_id')->references('id')->on('plan_pago_respaldo');

            // Información de auditoría
            $table->unsignedBigInteger('cuota_original_id'); // ID de la cuota original
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
        Schema::dropIfExists('cuota_respaldo');
    }
};
