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
        Schema::create('imagen', function (Blueprint $table) {
            $table->id();
            
            // Este campo guardará la ruta o el nombre del archivo de imagen
            $table->string('imagen'); 
            
            // Definición segura de la llave foránea
            $table->unsignedBigInteger('id_garantia');
            $table->foreign('id_garantia')->references('id')->on('garantia');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagen');
    }
};
