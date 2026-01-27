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
        Schema::create('egreso', function (Blueprint $table) {
            $table->id();
            $table->float('monto');
            $table->string('descripcion');
            $table->string('estado')->default(1);
            $table->date('fecha');
            $table->foreignId('id_usuario');
            $table->foreign('id_usuario')->references('id')->on('users');
            $table->foreignId('id_caja');
            $table->foreign('id_caja')->references('id')->on('caja');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egreso');
    }
};
