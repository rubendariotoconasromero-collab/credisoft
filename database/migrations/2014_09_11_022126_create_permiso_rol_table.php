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
        Schema::create('permiso_rol', function (Blueprint $table) {
            $table->unsignedBigInteger('id_permiso');
            $table->unsignedBigInteger('id_rol');
            $table->primary(['id_permiso', 'id_rol']);
            $table->foreign('id_permiso')->references('id')->on('permiso');
            $table->foreign('id_rol')->references('id')->on('rol');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permiso_rol');
    }
};
