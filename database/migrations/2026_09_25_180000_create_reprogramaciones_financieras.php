<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reprogramaciones_financieras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_solicitud')->unique();
            $table->unsignedBigInteger('id_plan_origen')->index();
            $table->unsignedBigInteger('id_plan_nuevo')->nullable();
            $table->unsignedBigInteger('id_orden')->nullable()->unique();
            $table->unsignedBigInteger('id_operacion_cobro')->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->date('fecha_corte');
            $table->json('snapshot');
            $table->unsignedBigInteger('id_usuario');
            $table->string('motivo_cancelacion', 500)->nullable();
            $table->unsignedBigInteger('cancelado_por')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('reprogramaciones_financieras'); }
};
