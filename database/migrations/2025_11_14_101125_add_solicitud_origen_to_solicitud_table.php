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
        Schema::table('solicitud', function (Blueprint $table) {
            $table->foreignId('id_solicitud_origen')
            ->nullable()
            ->constrained('solicitud') // Apunta a la misma tabla
            ->onDelete('set null') // Si se borra la original, esta no se borra
            ->after('id_cliente'); // O donde prefieras
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitud', function (Blueprint $table) {
            $table->dropForeign(['id_solicitud_origen']);
            $table->dropColumn('id_solicitud_origen');
        });
    }
};
