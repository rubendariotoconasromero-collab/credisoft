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
        Schema::create('socios', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellidos', 100)->nullable();
            $table->string('ci', 20)->nullable()->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('estado')->default(1); // 1: Activo, 0: Inactivo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socios');
    }
};
