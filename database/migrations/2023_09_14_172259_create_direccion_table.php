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
        Schema::create('direccion', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->string('departamento');
            $table->string('ciudad')->nullable();
            $table->string('zona')->nullable();
            $table->string('descripcion');
            $table->string('referencia')->nullable();
            $table->foreignId('id_cliente')->nullable();
            $table->foreign('id_cliente')->references('id')->on('cliente');
            $table->foreignId('id_codeudor')->nullable();
            $table->foreign('id_codeudor')->references('id')->on('codeudor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direccion');
    }
};
