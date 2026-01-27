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
        Schema::create('codeudor', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->date('fecha_nacimiento');
            $table->string('ci');
            $table->string('lugar_expedicion');
            $table->string('sexo');
            $table->string('estado_civil');
            $table->string('actividad');
            $table->string('vivienda');
            $table->string('imagen')->nullable();
            $table->float('ingreso_mensual');
            $table->string('tipo');
            $table->integer('estado')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codeudor');
    }
};
