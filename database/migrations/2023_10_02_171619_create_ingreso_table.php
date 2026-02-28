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
        Schema::create('ingreso', function (Blueprint $table) {
            $table->id();
            
            // Cambiado a decimal para cálculos exactos de dinero
            $table->decimal('monto', 12, 2); 
            
            $table->string('descripcion');
            $table->string('estado')->default('1'); // Consistente con el tipo string de tu BD
            $table->date('fecha');
            
            // Definición estructurada de llaves foráneas
            $table->unsignedBigInteger('id_usuario');
            $table->foreign('id_usuario')->references('id')->on('users');
            
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
        Schema::dropIfExists('ingreso');
    }
};
