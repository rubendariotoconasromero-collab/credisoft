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
        Schema::table('motivo_ingreso', function (Blueprint $table) {
            // Agregamos el campo 'tipo', por defecto será 'caja'
            $table->string('tipo', 20)->default('caja')->after('nombre'); 
        });

        Schema::table('motivo_gasto', function (Blueprint $table) {
            $table->string('tipo', 20)->default('caja')->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('motivo_ingreso', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        Schema::table('motivo_gasto', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
