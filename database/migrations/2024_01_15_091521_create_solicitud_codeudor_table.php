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
        Schema::create('solicitud_codeudor', function (Blueprint $table) {
            $table->unsignedBigInteger('id_codeudor');
            $table->unsignedBigInteger('id_solicitud');
            $table->primary(['id_codeudor', 'id_solicitud']);
            $table->foreign('id_codeudor')->references('id')->on('codeudor');
            $table->foreign('id_solicitud')->references('id')->on('solicitud');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_codeudor');
    }
};
