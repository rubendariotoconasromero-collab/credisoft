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
        Schema::create('transferencia_caja_boveda', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_transferencia'); // Ej: boveda_a_caja, caja_a_boveda
            $table->string('descripcion');
            
            // Subimos la precisión a 12,2 para mantener el estándar
            $table->decimal('monto', 12, 2); 
            
            $table->datetime('fecha');

            // Definición estructurada de llaves foráneas
            $table->unsignedBigInteger('id_boveda');
            $table->foreign('id_boveda')->references('id')->on('boveda');

            $table->unsignedBigInteger('id_caja');
            $table->foreign('id_caja')->references('id')->on('caja');
                        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferencia_caja_boveda');
    }
};
