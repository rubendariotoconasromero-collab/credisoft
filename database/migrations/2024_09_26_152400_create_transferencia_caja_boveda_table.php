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
            $table->string('tipo_transferencia'); // interno
            $table->string('descripcion');
            $table->decimal('monto', 10, 2);
            $table->datetime('fecha'); // interno

            $table->foreignId('id_boveda');
            $table->foreign('id_boveda')->references('id')->on('boveda');

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
        Schema::dropIfExists('transferencia_caja_boveda');
    }
};
