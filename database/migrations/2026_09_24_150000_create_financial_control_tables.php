<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bloqueo_financiero', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
        });
        DB::table('bloqueo_financiero')->insert(['id' => 1]);
        Schema::create('caja_cierres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_caja')->unique()->constrained('caja');
            $table->foreignId('id_boveda')->constrained('boveda');
            $table->foreignId('id_usuario')->constrained('users');
            $table->decimal('efectivo_retornado', 12, 2);
            $table->json('resumen');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_cierres');
        Schema::dropIfExists('bloqueo_financiero');
    }
};
