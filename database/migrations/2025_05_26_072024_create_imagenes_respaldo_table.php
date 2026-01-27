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
        Schema::create('imagenes_respaldo', function (Blueprint $table) {
            $table->id();
            
            // Relación con la tabla padre 'respaldo'
            // Asegúrate de que tu tabla padre se llame 'respaldo' (singular) como indicaba tu error SQL anterior
            $table->unsignedBigInteger('id_respaldo');
            $table->foreign('id_respaldo')
                  ->references('id')
                  ->on('respaldo')
                  ->onDelete('cascade'); 

            $table->string('imagen'); // Nombre del archivo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagenes_respaldo');
    }
};
