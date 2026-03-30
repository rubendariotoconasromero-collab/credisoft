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
        Schema::table('movimientos_boveda', function (Blueprint $table) {
            $table->foreignId('id_socio')->nullable()->constrained('socios');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_boveda', function (Blueprint $table) {
            $table->dropForeign(['id_socio']);
            $table->dropColumn('id_socio');
        });
    }
};
